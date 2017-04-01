#!/bin/bash
#
# collect nfs server stats
# to enable, add this to snmpd.conf :
# extend nfs-server /path/to/this/file/nfs-server.sh

# setting
nfsd="/proc/net/rpc/nfsd"
# used to store the old values
store="/tmp/librenms_nfsd_server"
# used to store the current values
tmp="/tmp/librenms_nfsd_server.tmp"

# check if there is a server running here
if [ ! -f $nfsd ]; then
	echo "0"
	exit
fi

# check if this is init run
if [ ! -f $store ]; then
	echo "warning: could not find old file, its fine if this is inital run."
fi

# parse the nfsd file
# reply cache : hits, misses, nocache
cat $nfsd | sed -n 1p | awk '{print $2,$3,$4}' | tr " " "\n" > $tmp

# file handles : lookup, anon, ncachedir, ncachenondir, stale
cat $nfsd | sed -n 2p | awk '{print $2,$3,$4,$5,$6}' | tr " " "\n" >> $tmp

# io : read, write
cat $nfsd | sed -n 3p | awk '{print $2,$3}' | tr " " "\n" >> $tmp

# read ahead cache : 0-10%, 10-20%, ... 90-100%, not-found
cat $nfsd | sed -n 5p | awk '{print $3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13}' | tr " " "\n" >> $tmp

# net : all_reads, udp, tcp, tcp_conn
cat $nfsd | sed -n 6p | awk '{print $2,$3,$4,$5}' | tr " " "\n" >> $tmp

# rpc : calls, badfmt, badauth, badclnt
cat $nfsd | sed -n 7p | awk '{print $2,$4,$5,$6}' | tr " " "\n" >> $tmp

# nfsv2 : null, getattr, setattr, root, lookup, readlink, read, wrcache, write, create, remove, rename, link, symlink, mkdir, rmdir, readdir, fsstat
cat $nfsd | sed -n 8p | awk '{print $3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20}' | tr " " "\n" >> $tmp

# nfsv3 : null, getattr, setattr, lookup, access, readlink, read, write, create, mkdir, symlink, mknod, remove, rmdir, rename, link, readdir, readdirplus, fsstat, fsinfo, pathconf, commit
cat $nfsd | sed -n 9p | awk '{print $3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24}' | tr " " "\n" >> $tmp

# nfsv4 : null, compound
cat $nfsd | sed -n 10p | awk '{print $3,$4}' | tr " " "\n" >> $tmp

# nfsv4ops 
# OP_ACCESS, OP_CLOSE, OP_COMMIT, OP_CREATE,
# OP_DELEGPURGE, OP_DELEGRETURN, OP_GETATTR, OP_GETFH,
# OP_LINK, OP_LOCK, OP_LOCKT, OP_LOCKU,
# OP_LOOKUP, OP_LOOKUP_ROOT, OP_NVERIFY, OP_OPEN,
# OP_OPENATTR, OP_OPEN_CONFIRM, OP_OPEN_DOWNGRADE, OP_PUTFH,
# OP_PUTPUBFH, OP_PUTROOTFH, OP_READ, OP_READDIR,
# OP_READLINK, OP_REMOVE, OP_RENAME, OP_RENEW,
# OP_RESTOREFH, OP_SAVEFH, OP_SECINFO, OP_SETATTR,
# OP_SETCLIENTID, OP_SETCLIENTID_CONFIRM, OP_VERIFY, OP_WRITE,
# OP_RELEASE_LOCKOWNER, OP_BACKCHANNEL_CTL, OP_BIND_CONN_TO_SESSION, OP_EXCHANGE_ID,
# OP_CREATE_SESSION, OP_DESTROY_SESSION, OP_FREE_STATEID, OP_GET_DIR_DELEGATION,
# OP_GETDEVICEINFO, OP_GETDEVICELIST, OP_LAYOUTCOMMIT, OP_LAYOUTGET,
# OP_SECINFO_NO_NAME, OP_SEQUENCE, OP_SET_SSV, OP_TEST_STATEID,
# OP_WANT_DELEGATION, OP_DESTROY_CLIENTID, OP_RECLAIM_COMPLETE
cat $nfsd | sed -n 11p | awk '{print $6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,$27,$28,$29,$30,$31,$32,$33,$34,$35,$36,$37,$38,$39,$40,$41,$42,$43,$44,$45,$46,$47,$48,$49,$50,$51,$52,$53,$54,$55,$56,$57,$58,$59,$60}' | tr " " "\n" >> $tmp

# combine and subtract each line
output=""
while read a b ; do
        # check if its empty
        if [ -z "$a" ]; then
                output+="0|"
        else
                output+="$(($a-$b))|"
        fi
done < <(paste $tmp $store)

# copy the tmp over the new
cp -f $tmp $store

# clean up the tmp
rm $tmp

# base64 is required otherwise the string overflows
echo $output | base64
