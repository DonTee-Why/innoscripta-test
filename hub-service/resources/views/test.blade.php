<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HubService Realtime Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            background: #111827;
            color: #f9fafb;
        }

        h1 {
            margin-bottom: 1rem;
        }

        .controls {
            margin-bottom: 1rem;
        }

        input, button, select {
            padding: 0.6rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        #log {
            background: #1f2937;
            padding: 1rem;
            border-radius: 8px;
            white-space: pre-wrap;
            min-height: 300px;
            overflow-y: auto;
        }

        .success {
            color: #34d399;
        }

        .error {
            color: #f87171;
        }
    </style>
</head>
<body>
    <h1>HubService Realtime Test</h1>

    <div class="controls">
        <label>
            Country:
            <select id="country">
                <option value="USA">USA</option>
                <option value="Germany">Germany</option>
            </select>
        </label>

        <label>
            Employee ID:
            <input id="employeeId" type="number" value="1" min="1" />
        </label>

        <button id="connectBtn">Connect</button>
    </div>

    <div id="log"></div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        const logEl = document.getElementById('log');

        function log(message, type = 'info') {
            const time = new Date().toISOString();
            const line = `[${time}] ${message}\n`;
            const span = document.createElement('div');
            span.textContent = line;

            if (type === 'success') span.className = 'success';
            if (type === 'error') span.className = 'error';

            logEl.appendChild(span);
            logEl.scrollTop = logEl.scrollHeight;
        }

        let pusher = null;
        let subscriptions = [];

        function unsubscribeAll() {
            if (!pusher) return;

            subscriptions.forEach(channelName => {
                try {
                    pusher.unsubscribe(channelName);
                } catch (e) {
                    console.error(e);
                }
            });

            subscriptions = [];
        }

        document.getElementById('connectBtn').addEventListener('click', () => {
            const country = document.getElementById('country').value;
            const employeeId = document.getElementById('employeeId').value;

            if (!pusher) {
                Pusher.logToConsole = true;

                pusher = new Pusher('app-key', {
                    wsHost: 'localhost',
                    wsPort: 6001,
                    forceTLS: false,
                    enabledTransports: ['ws', 'wss'],
                    cluster: 'mt1',
                });

                pusher.connection.bind('connected', () => {
                    log('Connected to Soketi', 'success');
                });

                pusher.connection.bind('error', (err) => {
                    log(`Connection error: ${JSON.stringify(err)}`, 'error');
                });

                pusher.connection.bind('disconnected', () => {
                    log('Disconnected from Soketi', 'error');
                });
            }

            unsubscribeAll();

            const employeeChannelName = `country.${country}.employees`;
            const employeeDetailChannelName = `country.${country}.employee.${employeeId}`;
            const checklistChannelName = `country.${country}.checklists`;

            const employeeChannel = pusher.subscribe(employeeChannelName);
            const employeeDetailChannel = pusher.subscribe(employeeDetailChannelName);
            const checklistChannel = pusher.subscribe(checklistChannelName);

            subscriptions.push(employeeChannelName, employeeDetailChannelName, checklistChannelName);

            employeeChannel.bind('pusher:subscription_succeeded', () => {
                log(`Subscribed to ${employeeChannelName}`, 'success');
            });

            employeeDetailChannel.bind('pusher:subscription_succeeded', () => {
                log(`Subscribed to ${employeeDetailChannelName}`, 'success');
            });

            checklistChannel.bind('pusher:subscription_succeeded', () => {
                log(`Subscribed to ${checklistChannelName}`, 'success');
            });

            employeeChannel.bind('employee.created', data => {
                log(`[${employeeChannelName}] employee.created -> ${JSON.stringify(data, null, 2)}`);
            });

            employeeChannel.bind('employee.updated', data => {
                log(`[${employeeChannelName}] employee.updated -> ${JSON.stringify(data, null, 2)}`);
            });

            employeeChannel.bind('employee.deleted', data => {
                log(`[${employeeChannelName}] employee.deleted -> ${JSON.stringify(data, null, 2)}`);
            });

            employeeDetailChannel.bind('employee.created', data => {
                log(`[${employeeDetailChannelName}] employee.created -> ${JSON.stringify(data, null, 2)}`);
            });

            employeeDetailChannel.bind('employee.updated', data => {
                log(`[${employeeDetailChannelName}] employee.updated -> ${JSON.stringify(data, null, 2)}`);
            });

            employeeDetailChannel.bind('employee.deleted', data => {
                log(`[${employeeDetailChannelName}] employee.deleted -> ${JSON.stringify(data, null, 2)}`);
            });

            checklistChannel.bind('checklist.updated', data => {
                log(`[${checklistChannelName}] checklist.updated -> ${JSON.stringify(data, null, 2)}`);
            });
        });
    </script>
</body>
</html>