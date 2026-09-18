## Weechat BOT

The Weechat bot transport sends alerts to an IRC channel through the
Weechat bot UDP listener.

For the steps to enable the UDP listener, read [the project
site](https://github.com/sndrsmnk/weechatbot#udp-listener).

This transport also works with Gozerbot. Gozerbot has no IRC server
field. For Gozerbot, leave this field empty.

**Example:**
| Config | Example |
| ------ | ------- |
| Weechat Bot server | wcb.example.com |
| Weechat Bot port | 47774 |
| UDP listener Password | s00p3rzeeKRiT! |
| IRC server | IRCnet |
| IRC channel | #librenms |
