import socket
import re


def fpm_runner(command, args, path=None, host=None, port=None):
    retcode = 0

    fpmCmd = FCGI(path=path, host=host, port=port)

    fpmCmd.send_request(
        {
            "SCRIPT_FILENAME": command,
            "REQUEST_METHOD": "GET",
            "QUERY_STRING": args,
        }
    )

    allData = ""
    requestId, data, error, complete = fpmCmd.recv_message()
    while not complete:
        allData += data.decode("utf-8")
        if error:
            retcode = 1

        requestId, data, error, complete = fpmCmd.recv_message()

    retSearch = re.compile("(lnms_exit_status:)(\d+)\n")
    retMatch = retSearch.search(allData)
    if retMatch != None:
        retcode = int(retMatch.group(2))
        retSpan = retMatch.span()

        output = ""
        if retSpan[0] > 0:
            output += allData[: retSpan[0]]

        if retSpan[1] < len(allData):
            output += allData[retSpan[1] :]
    else:
        output = allData

    return retcode, output

class FCGI:
    __FCGI_VERSION_1 = 1

    __FCGI_RESPONDER = 1
    __FCGI_AUTHORIZER = 2
    __FCGI_FILTER = 3

    __FCGI_BEGIN_REQUEST = 1
    __FCGI_ABORT_REQUEST = 2
    __FCGI_END_REQUEST = 3
    __FCGI_PARAMS = 4
    __FCGI_STDIN = 5
    __FCGI_STDOUT = 6
    __FCGI_STDERR = 7
    __FCGI_DATA = 8
    __FCGI_GET_VALUES = 9
    __FCGI_GET_VALUES_RESULT = 10
    __FCGI_UNKNOWN_TYPE = 11

    __FCGI_HEADER_LEN = 8

    def __init__(self, host=None, port=None, path=None, timeout=10, keepalive=False):
        self.host = host
        self.port = int(port) if port != None else None
        self.path = path
        self.timeout = int(timeout)
        if keepalive:
            self.keepalive = 1
        else:
            self.keepalive = 0

        self.sock = None
        self.requests = 0
        self.requestId = 1

    def __encodeRecord(self, record_type, content, requestid):
        ret = bytearray([self.__FCGI_VERSION_1, record_type])
        ret.extend(requestid.to_bytes(2, "big"))
        ret.extend(len(content).to_bytes(2, "big"))
        ret.append(0)
        ret.append(0)

        if type(content) is bytearray or type(content) is bytes:
            return ret + content
        elif type(content) is str:
            return ret + content.encode("utf-8")

        raise Exception("Cannot encode the following content: {content}")

    def __encodeNameVal(self, name, val):
        nameStr = str(name)
        valStr = str(val)
        nameLen = len(nameStr)
        valLen = len(valStr)
        ret = bytearray()

        if nameLen < 128:
            ret.append(nameLen)
        else:
            ret.extend(nameLen.to_bytes(4, "big"))

        if valLen < 128:
            ret.append(valLen)
        else:
            ret.extend(valLen.to_bytes(4, "big"))

        return ret + nameStr.encode("utf-8") + valStr.encode("utf-8")

    def connect(self):
        try:
            if self.path != None:
                self.sock = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
                self.sock.settimeout(self.timeout)
                self.sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
                self.sock.connect(self.path)
            else:
                self.sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
                self.sock.settimeout(self.timeout)
                self.sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
                self.sock.connect(self.host, self.port)
        except socket.error:
            self.close()
            return False

        return True

    def close(self):
        self.sock.close()
        self.sock = None
        self.requests = 0
        self.requestId = 1

    def __recv(self, size):
        data = ""
        try:
            data = self.sock.recv(size)
        except socket.error:
            self.close()
        return data

    def send_request(self, nameValPairs={}, postData=""):
        if self.sock == None:
            self.connect()

        header = bytearray([0, self.__FCGI_RESPONDER, self.keepalive, 0, 0, 0, 0, 0])
        request = self.__encodeRecord(self.__FCGI_BEGIN_REQUEST, header, self.requestId)

        if nameValPairs:
            params = bytearray()
            for (name, val) in nameValPairs.items():
                params.extend(self.__encodeNameVal(name, val))
            request.extend(
                self.__encodeRecord(self.__FCGI_PARAMS, params, self.requestId)
            )

        request.extend(self.__encodeRecord(self.__FCGI_PARAMS, "", self.requestId))

        if postData:
            request.extend(
                self.__encodeRecord(self.__FCGI_STDIN, postData, self.requestId)
            )
        request.extend(self.__encodeRecord(self.__FCGI_STDIN, "", self.requestId))

        self.requestId += 1
        self.requests += 1

        self.sock.send(request)

    def recv_message(self):
        if self.requests == 0 or self.sock == None:
            return None, None, True, True

        header = self.__recv(self.__FCGI_HEADER_LEN)
        if not header:
            return None, None, True, True

        complete = False

        messageType = header[1]
        requestId = int.from_bytes(header[2:4], "big")
        dataLen = int.from_bytes(header[4:6], "big")
        padLen = header[6]
        data = bytearray()
        dataLeft = dataLen

        while dataLeft > 0:
            buffer = self.__recv(dataLeft)
            bufferLen = len(buffer)
            if bufferLen == 0:
                break
            dataLeft -= bufferLen
            data.extend(buffer)

        if padLen > 0:
            self.__recv(padLen)

        if messageType == self.__FCGI_STDERR:
            error = True
        else:
            error = False

        if messageType == self.__FCGI_END_REQUEST:
            complete = True
            data = int.from_bytes(data[0:4], "big")
            self.requests -= 1

            if self.requests == 0 and self.keepalive == 0:
                self.close()

        return requestId, data, error, complete
