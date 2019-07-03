source: Alerting/Introduction.md
path: blob/master/doc/

# Introduction

To get started, you first need some alert rules which will react to
changes with your devices before raising an alert.

[Creating alert rules](Rules.md)

After that you also need to tell LibreNMS how to notify you when an
alert is raised, this is done using `Alert Transports`.

[Configuring alert transports](Transports.md)

The next step is not strictly required but most people find it
useful. Creating custom alert templates will help you get the benefit
out of the alert system in general. Whilst we include a default
template, it is limited in the data that you will receive in the alerts.

[Configuring alert templates](Templates.md)

### Managing alerts

When an alert has triggered you will see these in the Alerts ->
Notifications page within the Web UI.

This list has a couple of options available to it and we'll explain
what these are here.

#### ACK

This column provides you visibility on the status of the alert:

![ack alert](img/ack.png) This alert is currently active and sending
alerts. Click this icon to acknowledge the alert.

![unack alert](img/unack.png) This alert is currently acknowledged
until the alert clears. Click this icon to un-acknowledge the alert.

![unack alert until fault worsens](img/nunack.png) This alert is
currently acknowledged until the alert worsens or gets
better, at which stage it will be automatically unacknowledged and
alerts will resume. Click this icon to un-acknowledge the alert.

#### Notes

![alert notes](img/notes.png) This column will allow you access to the
acknowledge/unacknowledge notes for this alert.
