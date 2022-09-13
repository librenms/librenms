<?php

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Snmptrap extends Transport
{
	public function deliverAlert($obj,$opts)
	{
		$host = $this->config['snmptrap-destination-host'];
		if (! empty($this->config['snmptrap-destination-port']) ) {
			$port = $this->config['snmptrap-destination-port'];
		} else {
			/* Default SNMP trap port */
			$port = '162';
		}
		$transport = $this->config['snmptrap-transport'];
		$trapdefinition = $this->config['snmptrap-definition'];
		$pdu = $this->config['snmptrap-pdu'];
		$community = $this->config['snmptrap-community'];
		$binary = $this->config['snmptrap-path'];
		$mibdir = $this->config['mib-dir'];


		return $this->contactSnmptrap($binary,$mibdir,$transport,$host,$port,$community,$trapdefinition,$pdu,$obj);
	}

	private function contactSnmptrap($binary,$mibdir,$transport,$host,$port,$community,$trapdefinition,$pdu,$obj)
	{
		switch ($pdu) {
			case 'TRAPv2':
				$opts = '-v 2c';
				break;
			case 'INFORM':
				$opts = '-v 2c -Ci';
				break;
			default:
				echo 'This should not happen!!!';
				break;
		}

		$msgsingle = preg_replace('~\R~',' ',$obj['msg']);

		putenv('SNMP_PERSISTENT_FILE=/tmp/snmpapp.conf.$USER');

		#exec('/usr/bin/echo'
		exec($binary
			. ' ' . $opts
			. ' ' . '-M +' . $mibdir
			. ' -c ' . $community
			. ' ' . $transport . ":" . $host . ':' . $port
			. ' ' . '\"\"' . ' ' . $trapdefinition . ' '
			. $msgsingle , $output, $cmdexitcode
		);

		#var_dump($output);
		if ( $cmdexitcode == 0 ) {
			return true;
	       	} else {
			return false; 
		}
	}

	public static function configTemplate()
	{
		return [
			'config' => [
				[
					'title' => 'Destination host',
					'name' => 'snmptrap-destination-host',
					'descr' => 'Hostname or IP of the host that will receive the trap.',
					'type' => 'text',
				],
				[
					'title' => 'Destination port',
					'name' => 'snmptrap-destination-port',
					'descr' => 'Port to be used. Defaults to 162 when not specified.',
					'type' => 'text',
				],
				[
					'title' => 'SNMP Trap transport',
					'name' => 'snmptrap-transport',
					'descr' => 'UDP or TCP, UDP is default.',
					'type' => 'select',
					'options' => [
						'UDP' => 'UDP',
						'TCP' => 'TCP',
					],
				],
				[
					'title' => 'Community',
					'name' => 'snmptrap-community',
					'descr' => 'SNMP community',
					'type' => 'text',
				],
				[
					'title' => 'Trap Definition',
					'name' => 'snmptrap-definition',
					'descr' => 'For v2c it should include sysUpTime and trap OID',
					'type' => 'text',
				],
				[
					'title' => 'PDU',
					'name' => 'snmptrap-pdu',
					'descr' => 'Type of message to send',
					'type' => 'select',
					'options' => [
						'TRAPv2' => 'TRAPv2',
						'INFORM' => 'INFORM',
					],
				],
				[
					'title' => 'Binary path',
					'name' => 'snmptrap-path',
					'descr' => 'snmptrap binary path',
					'type' => 'text',
				],
				[
					'title' => 'MIB file path',
					'name' => 'mib-dir',
					'descr' => 'Directory from where to load the MIB entities. Yes, we want to do it properly :)',
					'type' => 'text',
				],
			],
			'validation' => [
				'snmptrap-destination-host' => 'required|string',
				'snmptrap-destination-port' => 'numeric',
				'snmptrap-transport' => 'in:UDP,TCP',
				'snmptrap-community' => 'required|string',
				'snmptrap-definition' => 'required|string',
				'snmptrap-pdu' => 'in:TRAPv2,INFORM',
				'snmptrap-path' => 'required|string',
				'mib-dir' => 'required|string',
			],
		];
	}

}
