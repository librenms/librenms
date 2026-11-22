<x-option-bar :name="__('NTP Peers')" :options="$peerViewOptions" :selected="$peerView" />
<x-option-bar :name="__('Graphs')" :options="$peerGraphOptions" :selected="$peerGraph" />

@include('apps.ntp-peers', ['showDevice' => true])
