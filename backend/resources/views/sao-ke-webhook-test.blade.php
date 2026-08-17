<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test webhook sao kê ngân hàng</title>
    <style>
        :root {
            --bg: #f4f1ea;
            --card: #fffdf8;
            --ink: #1c1914;
            --muted: #6b6458;
            --line: #e4ddd0;
            --accent: #1f6b4a;
            --accent-hover: #18563c;
            --danger: #9b2c2c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: var(--bg);
            color: var(--ink);
        }
        main {
            max-width: 920px;
            margin: 0 auto;
            padding: 32px 20px 48px;
        }
        h1 { margin: 0 0 8px; font-size: 1.5rem; }
        p.lead { margin: 0 0 20px; color: var(--muted); }
        code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.86em;
            background: #efe8db;
            padding: 1px 6px;
            border-radius: 4px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }
        textarea, input[type="text"] {
            width: 100%;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 13px;
            line-height: 1.45;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            color: var(--ink);
        }
        textarea { min-height: 320px; resize: vertical; }
        .row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 14px 0 0;
        }
        button {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-ghost { background: #efe8db; color: var(--ink); }
        .token-field { margin-top: 14px; }
        .hint { margin: 8px 0 0; color: var(--muted); font-size: 0.9rem; }
        pre {
            margin: 16px 0 0;
            padding: 14px;
            border-radius: 8px;
            background: #1c1914;
            color: #f4efe6;
            overflow: auto;
            font-size: 13px;
            min-height: 80px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        pre.is-error { background: #3b1414; }
        pre.is-ok { background: #123226; }
        .status { margin-top: 12px; font-weight: 600; }
        .status.error { color: var(--danger); }
        .status.ok { color: var(--accent); }
    </style>
</head>
<body>
<main>
    <h1>Test webhook sao kê ngân hàng</h1>
    <p class="lead">
        Dán JSON payload rồi gửi tới
        <code>{{ $webhookUrl }}</code>.
        Cột <code>id</code> sẽ lưu thành <code>item_id</code>;
        <code>content</code> chỉ giữ mã <code>NAP...ECOIN</code> hoặc <code>PAY...ECOIN</code>.
    </p>

    <div class="card">
        <label for="payload">JSON payload</label>
        <textarea id="payload">{{ $sampleJson }}</textarea>

        @if ($requiresToken)
            <div class="token-field">
                <label for="token">Webhook token</label>
                <input id="token" type="text" placeholder="SAO_KE_WEBHOOK_TOKEN" autocomplete="off">
                <p class="hint">Endpoint đang yêu cầu token. Gửi kèm header Authorization.</p>
            </div>
        @endif

        <div class="row">
            <button class="btn-primary" id="send" type="button">Gửi webhook</button>
            <button class="btn-ghost" id="reset" type="button">Khôi phục mẫu</button>
        </div>
        <p class="status" id="status"></p>
        <pre id="response">Chưa gửi.</pre>
    </div>
</main>

<script>
    const webhookUrl = @json($webhookUrl);
    const sampleJson = @json($sampleJson);
    const requiresToken = @json($requiresToken);
    const payloadEl = document.getElementById('payload');
    const statusEl = document.getElementById('status');
    const responseEl = document.getElementById('response');
    const sendBtn = document.getElementById('send');
    const tokenEl = document.getElementById('token');

    document.getElementById('reset').addEventListener('click', () => {
        payloadEl.value = sampleJson;
        statusEl.textContent = '';
        statusEl.className = 'status';
        responseEl.className = '';
        responseEl.textContent = 'Chưa gửi.';
    });

    sendBtn.addEventListener('click', async () => {
        let body;
        try {
            body = JSON.parse(payloadEl.value);
        } catch (error) {
            statusEl.className = 'status error';
            statusEl.textContent = 'JSON không hợp lệ.';
            responseEl.className = 'is-error';
            responseEl.textContent = String(error);
            return;
        }

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
        const token = tokenEl ? tokenEl.value.trim() : '';
        if (token) {
            headers.Authorization = 'Apikey ' + token;
        }

        sendBtn.disabled = true;
        statusEl.className = 'status';
        statusEl.textContent = 'Đang gửi...';
        responseEl.className = '';
        responseEl.textContent = '';

        try {
            const res = await fetch(webhookUrl, {
                method: 'POST',
                headers,
                body: JSON.stringify(body),
            });
            const text = await res.text();
            let pretty = text;
            try {
                pretty = JSON.stringify(JSON.parse(text), null, 2);
            } catch (_) {}

            const ok = res.ok && !pretty.includes('"status": false');
            statusEl.className = ok ? 'status ok' : 'status error';
            statusEl.textContent = 'HTTP ' + res.status + (ok ? ' — đã xử lý.' : ' — có lỗi.');
            responseEl.className = ok ? 'is-ok' : 'is-error';
            responseEl.textContent = pretty;
        } catch (error) {
            statusEl.className = 'status error';
            statusEl.textContent = 'Không gửi được request.';
            responseEl.className = 'is-error';
            responseEl.textContent = String(error);
        } finally {
            sendBtn.disabled = false;
        }
    });
</script>
</body>
</html>
