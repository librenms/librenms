{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<svg width="100%" height="100%" viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"
     onload="init(evt)">
  <g id="graph">
    <rect id="bg" x1="0" y1="0" width="100%" height="100%" fill="white"/>
    <line id="axis_x" x1="0" y1="0" x2="0" y2="100%" fill="black" stroke="black"/>
    <line id="axis_y" x1="0" y1="100%" x2="100%" y2="100%" fill="black" stroke="black"/>
    <path id="graph_out" d="M0 {{ $height }} L 0 {{ $height }}" fill="none" stroke="blue" stroke-opacity="0.8"/>
    <path id="graph_in"  d="M0 {{ $height }} L 0 {{ $height }}" fill="none" stroke="green" stroke-opacity="0.8"/>
    <path id="grid"  d="M0 {{ $height / 4 * 1 }} L {{ $width }} {{ $height / 4 * 1 }} M0 {{ $height / 4 * 2 }} L {{ $width }} {{ $height / 4 * 2 }} M0 {{ $height / 4 * 3 }} L {{ $width . ' ' . ($height / 4 * 3) }}" stroke="gray" stroke-opacity="0.5"/>
    <text id="grid_txt1" x="{{ $width }}" y="{{ $height / 4 * 1 }}" fill="gray" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="6" text-anchor="end"> </text>
    <text id="grid_txt2" x="{{ $width }}" y="{{ $height / 4 * 2 }}" fill="gray" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="6" text-anchor="end"> </text>
    <text id="grid_txt3" x="{{ $width }}" y="{{ $height / 4 * 3 }}" fill="gray" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="6" text-anchor="end"> </text>
    <text id="graph_in_lbl" x="5" y="8" fill="green" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="7">In</text>
    <text id="graph_out_lbl" x="5" y="16" fill="blue" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="7">Out</text>
    <text id="graph_in_txt" x="20" y="8" fill="green" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="7"> </text>
    <text id="graph_out_txt" x="20" y="16" fill="blue" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="7"> </text>
    <text id="ifname" x="{{ $width - 2 }}" y="8" fill="#435370" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="9" text-anchor="end">{{ $graphTitle }}</text>
    <text id="hostname" x="{{ $width - 2 }}" y="14" fill="#435370" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="6" text-anchor="end">{{ $deviceName }}</text>
    <text id="switch_unit" x="{{ $width * 0.48 }}" y="5" fill="#435370" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="4" text-decoration="underline">Switch to bytes/s</text>
    <text id="switch_scale" x="{{ $width * 0.48 }}" y="11" fill="#435370" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="4" text-decoration="underline">AutoScale ({{ $scaleType }})</text>
    <text id="datetime" x="{{ $width * 0.33 }}" y="5" fill="black" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="4"> </text>
    <text id="graphlast" x="{{ $width * 0.48 }}" y="17" fill="black" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="4">Graph shows last {{ $graphDuration }} seconds</text>
    <text id="cachewarning" x="{{ $width * 0.48 }}" y="22" fill="darkorange" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="4" visibility="hidden">Caching may be in effect (<tspan id="cacheinterval">?</tspan>s)</text>
    <polygon id="axis_arrow_x" fill="black" stroke="black" points="{{ $width . ',' . $height }} {{ ($width - 2) . ',' . ($height - 2) }} {{ ($width - 2) . ',' . $height }}"/>
    <text id="error" x="{{ $width * 0.5 }}" y="{{ $height * 0.4 }}" visibility="hidden" fill="blue" font-family="Arial" font-size="4" text-anchor="middle">{{ $errorText }}</text>
    <text id="collect_initial" x="{{ $width * 0.5 }}" y="{{ $height * 0.4 }}" visibility="hidden" fill="gray" font-family="Tahoma, Verdana, Arial, Helvetica, sans-serif" font-size="4" text-anchor="middle">Collecting initial data, please wait...</text>
  </g>
  <script type="application/ecmascript">
    {{--  adapted from
    * @author     T. Lechat <dev@lechat.org>, Manuel Kasper <mk@neon1.net>, Jonathan Watt <jwatt@jwatt.org>
    * @copyright  2004-2006 T. Lechat <dev@lechat.org>, Manuel Kasper <mk@neon1.net>, Jonathan Watt <jwatt@jwatt.org>
    * @license    BSD
    --}}

    <![CDATA[
    let svgDoc = null;
    let lastIn = 0;
    let lastOut = 0;
    let lastTime = 0;
    let lastReal = 0;
    let realInterval = 0;
    let max = 0;
    const plotIn = [];
    const plotOut = [];

    const maxNumPoints = {{ $nbPlot }};
    const height = {{ $height }};
    const step = {{ $width }} / maxNumPoints;
    const intervalMs = Math.max(1, Math.round({{ $timeInterval }} * 1000));

    let unit = 'bits';
    let scaleType = '{{ $scaleType }}';

    function init(evt) {
      svgDoc = evt.target.ownerDocument;
      svgDoc.getElementById('switch_unit').addEventListener('mousedown', switchUnit, false);
      svgDoc.getElementById('switch_scale').addEventListener('mousedown', switchScale, false);

      fetchData();
    }

    function switchUnit() {
      svgDoc.getElementById('switch_unit').firstChild.data = `Switch to ${unit}/s`;
      unit = unit === 'bits' ? 'bytes' : 'bits';
    }

    function switchScale() {
      scaleType = scaleType === 'up' ? 'follow' : 'up';
      svgDoc.getElementById('switch_scale').firstChild.data = `AutoScale (${scaleType})`;
    }

    async function fetchData() {
      try {
        const response = await fetch(@json($fetchLink), { cache: 'no-store' });
        const payload = await response.text();
        plotData(payload);
      } catch (error) {
        handleError();
        scheduleFetch();
      }
    }

    function scheduleFetch() {
      setTimeout(fetchData, intervalMs);
    }

    function plotData(payload) {
      const now = new Date();
      const datetime = `${now.getMonth() + 1}/${now.getDate()}/${now.getFullYear()} ${lz(now.getHours())}:${lz(now.getMinutes())}:${lz(now.getSeconds())}`;
      svgDoc.getElementById('datetime').firstChild.data = datetime;

      const parts = payload.split('|');
      if (parts.length < 3) {
        handleError();
        scheduleFetch();
        return;
      }

      const ugmt = parseFloat(parts[0]);
      const ifin = parseInt(parts[1], 10);
      const ifout = parseInt(parts[2], 10);

      if (!isNumber(ugmt) || !isNumber(ifin) || !isNumber(ifout)) {
        handleError();
        scheduleFetch();
        return;
      }

      let diffTime = ugmt - lastTime;
      const diffIn = ifin - lastIn;
      const diffOut = ifout - lastOut;

      if (diffIn === 0 && diffOut === 0) {
        handleError('cachewarning');
      } else {
        const diffReal = ugmt - lastReal;
        lastReal = ugmt;
        if (realInterval === 0) {
          if (diffReal < 10000) {
            realInterval = diffReal;
          }
        } else {
          realInterval = (diffReal + realInterval) / 2;
        }
      }

      if (diffTime === 0) {
        diffTime = 1;
      }

      lastTime = ugmt;
      lastIn = ifin;
      lastOut = ifout;

      switch (plotIn.length) {
        case 0:
          svgDoc.getElementById('collect_initial').setAttributeNS(null, 'visibility', 'visible');
          plotIn[0] = diffIn / diffTime;
          plotOut[0] = diffOut / diffTime;
          scheduleFetch();
          return;
        case 1:
          svgDoc.getElementById('collect_initial').setAttributeNS(null, 'visibility', 'hidden');
          break;
        case maxNumPoints:
          plotIn.shift();
          plotOut.shift();
      }

      const currentIn = diffIn / diffTime;
      const currentOut = diffOut / diffTime;
      plotIn.push(currentIn);
      plotOut.push(currentOut);

      if (currentIn !== 0 && currentOut !== 0) {
        svgDoc.getElementById('graph_in_txt').firstChild.data = formatSpeed(currentIn, unit);
        svgDoc.getElementById('graph_out_txt').firstChild.data = formatSpeed(currentOut, unit);
      }

      if (scaleType === 'up') {
        max = Math.max(max, currentIn, currentOut);
      } else {
        max = 0;
        for (let i = 0; i < plotIn.length; i++) {
          max = Math.max(max, plotIn[i], plotOut[i]);
        }
      }

      let roundedMax;
      if (unit === 'bits') {
        roundedMax = 12500;
        let i = 0;
        while (max > roundedMax) {
          i++;
          if (i && i % 4 === 0) {
            roundedMax *= 1.25;
          } else {
            roundedMax *= 2;
          }
        }
      } else {
        roundedMax = 10240;
        let i = 0;
        while (max > roundedMax) {
          i++;
          if (i && i % 4 === 0) {
            roundedMax *= 1.25;
          } else {
            roundedMax *= 2;
          }

          if (i === 8) {
            roundedMax *= 1.024;
          }
        }
      }

      const scale = height / roundedMax;

      svgDoc.getElementById('grid_txt1').firstChild.data = formatSpeed(3 * roundedMax / 4, unit);
      svgDoc.getElementById('grid_txt2').firstChild.data = formatSpeed(2 * roundedMax / 4, unit);
      svgDoc.getElementById('grid_txt3').firstChild.data = formatSpeed(roundedMax / 4, unit);

      let pathIn = `M 0 ${height - (plotIn[0] * scale)}`;
      let pathOut = `M 0 ${height - (plotOut[0] * scale)}`;
      for (let i = 1; i < plotIn.length; i++) {
        if (plotIn[i] !== 0 && plotOut[i] !== 0) {
          const x = step * i;
          const yIn = height - (plotIn[i] * scale);
          const yOut = height - (plotOut[i] * scale);
          pathIn += ` L${x} ${yIn}`;
          pathOut += ` L${x} ${yOut}`;
        }
      }

      svgDoc.getElementById('error').setAttributeNS(null, 'visibility', 'hidden');
      svgDoc.getElementById('graph_in').setAttributeNS(null, 'd', pathIn);
      svgDoc.getElementById('graph_out').setAttributeNS(null, 'd', pathOut);

      scheduleFetch();
    }

    function handleError(type) {
      if (type === 'cachewarning') {
        svgDoc.getElementById('cachewarning').setAttributeNS(null, 'visibility', 'visible');
        if (realInterval !== 0) {
          svgDoc.getElementById('cacheinterval').firstChild.data = Math.round(realInterval);
        }
        return;
      }

      svgDoc.getElementById('error').setAttributeNS(null, 'visibility', 'visible');
    }

    function isNumber(value) {
      return typeof value === 'number' && Number.isFinite(value);
    }

    function formatSpeed(speed, nextUnit) {
      if (nextUnit === 'bits') {
        return formatSpeedBits(speed);
      }
      if (nextUnit === 'bytes') {
        return formatSpeedBytes(speed);
      }
      return '';
    }

    function formatSpeedBits(speed) {
      if (speed < 125000) {
        return `${Math.round(speed / 125)} Kbps`;
      }
      if (speed < 125000000) {
        return `${Math.round(speed / 1250) / 100} Mbps`;
      }
      return `${Math.round(speed / 1250000) / 100} Gbps`;
    }

    function formatSpeedBytes(speed) {
      if (speed < 1048576) {
        return `${Math.round(speed / 10.24) / 100} KB/s`;
      }
      if (speed < 1073741824) {
        return `${Math.round(speed / 10485.76) / 100} MB/s`;
      }
      return `${Math.round(speed / 10737418.24) / 100} GB/s`;
    }

    function lz(value) {
      return value < 0 || value > 9 ? `${value}` : `0${value}`;
    }
    ]]>
  </script>
</svg>
