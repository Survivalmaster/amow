<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Account Banned</title>
        <style>
            html, body {
                background-color: #F7F8FC;
                color: #1b1b18;
                font-family: ui-sans-serif, system-ui, sans-serif;
                font-weight: 400;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .content {
                text-align: center;
                max-width: 720px;
                padding: 2rem;
            }

            .title {
                font-size: 32px;
                padding: 20px;
                color: #1b1b18;
            }

            .subtitle {
                font-size: 16px;
                color: #6b6b67;
                line-height: 1.6;
            }

            .reason {
                margin-top: 1.5rem;
                font-size: 16px;
                color: #1b1b18;
                line-height: 1.7;
            }

            .appeal {
                margin-top: 1.25rem;
                font-size: 15px;
                color: #6b6b67;
                line-height: 1.7;
            }

            .discord-link {
                color: #1b1b18;
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title">Account Banned</div>
                <div class="subtitle">Your account has been banned from accessing this service.</div>
                <div class="reason">{{ $user->banned_reason ?: 'No reason was provided for this ban.' }}</div>
                <div class="appeal">
                    If you think the ban is incorrect or you would like to appeal this, query this in our discord.
                    <br>
                    <a class="discord-link" href="https://discord.gg/vC3hpcfRcq">https://discord.gg/vC3hpcfRcq</a>
                </div>
            </div>
        </div>
    </body>
</html>
