# Operations

Alert **Operations** let you reuse the same “who to notify and when” behavior across multiple Alert Rules.

Instead of configuring delays, repeats, and transport targets separately on every rule, you create an Operation once, then assign it to any rule.

It's not necessary to create an operation, without one an alert rule will still raise an alert, it just won't send a notification.

## What an Operation is

An Operation is a named set of one or more **segments**.

Each segment provides the ability to configure the following:

- **Steps from** At what point in the notification process does this segment start
- **Steps to** At what point in the notification process does this segement end
- **Start** The delay before notifications start (in seconds)
- **Step duration** The time that passes between each step.

In practice, most people start with a single segment. Let's say we want to send one notification straight away, we would 
configure the operation as follows:

Steps from: 1
Steps to: 1
Start: 0
Step duration: 60

## Transports used by Operations

Each segment contains its own list of notification targets:

- **Transports** (Slack, email, Telegram, etc.)
- **Transport groups** (a reusable group of transports)

This means you can:

- Send to one set of transports early (first segment)
- Send to a wider set later (second segment)

## Assigning an Operation to a Rule

When editing/creating an Alert Rule, you can choose an **Operation**.

- If you assign an Operation, the rule uses that Operation’s segments and transports to decide how notifications are sent.

## Examples

Send 5 notifications every 60 seconds to the NOC via email before escalating to managers via slack once:

**Segment one:**
Steps from: 1
Steps to: 5
Start: 0
Step duration: 60
Transport: Email

**Segement two:**
Steps from: 6
Steps to: 6
Start: 0
Step duration: 60
Transport: Slack

Send continuous notifications until the alert has recovered or is acknowledged:

**Segment one:**
Steps from: 1
Steps to:
Start: 0
Step duration: 60
Transport: Email and Slack

## Managing Operations

Operations are intended to be reusable:

- Name an operation to describe the policy (for example, “Critical paging escalation”).
- Update segments/transports once to affect every rule that uses it.
