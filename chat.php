<?php
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>채팅 - <?php echo sanitize($_SESSION['username']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .sidebar-header h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .sidebar-header .username {
            font-size: 14px;
            opacity: 0.9;
        }

        .online-users {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }

        .online-users h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-item {
            padding: 8px 10px;
            margin-bottom: 5px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .user-item::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #4caf50;
            border-radius: 50%;
            margin-right: 10px;
        }

        .logout-btn {
            margin: 15px;
            padding: 10px;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: #d32f2f;
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 20px;
            background: white;
            border-bottom: 1px solid #e0e0e0;
        }

        .chat-header h1 {
            font-size: 20px;
            color: #333;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #fafafa;
        }

        .message {
            margin-bottom: 15px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.own {
            text-align: right;
        }

        .message .username {
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 3px;
        }

        .message.own .username {
            color: #764ba2;
        }

        .message .bubble {
            display: inline-block;
            max-width: 60%;
            padding: 10px 15px;
            border-radius: 18px;
            background: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            word-wrap: break-word;
        }

        .message.own .bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .message .time {
            font-size: 11px;
            color: #999;
            margin-top: 3px;
        }

        .input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .input-form {
            display: flex;
            gap: 10px;
        }

        #messageInput {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        #messageInput:focus {
            border-color: #667eea;
        }

        #sendBtn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        #sendBtn:hover {
            transform: scale(1.05);
        }

        #sendBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .typing-indicator {
            padding: 10px 20px;
            font-size: 12px;
            color: #999;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .message .bubble {
                max-width: 80%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>채팅방</h2>
                <div class="username"><?php echo sanitize($_SESSION['username']); ?></div>
            </div>

            <div class="online-users">
                <h3>온라인 사용자</h3>
                <div id="userList"></div>
            </div>

            <button class="logout-btn" onclick="logout()">로그아웃</button>
        </div>

        <div class="chat-container">
            <div class="chat-header">
                <h1>전체 채팅</h1>
            </div>

            <div class="messages" id="messages"></div>

            <div class="input-container">
                <form class="input-form" id="messageForm">
                    <input type="text" id="messageInput" placeholder="메시지를 입력하세요..." autocomplete="off" maxlength="500">
                    <button type="submit" id="sendBtn">전송</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const currentUsername = '<?php echo sanitize($_SESSION['username']); ?>';
        let lastMessageId = 0;
        let isLoading = false;

        // 초기 메시지 로드
        async function loadMessages() {
            try {
                const response = await fetch('api.php?action=load');
                const data = await response.json();

                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        displayMessage(msg);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                    scrollToBottom();
                }
            } catch (error) {
                console.error('메시지 로드 실패:', error);
            }
        }

        // 새 메시지 확인
        async function checkNewMessages() {
            if (isLoading) return;

            try {
                isLoading = true;
                const response = await fetch(`api.php?action=get&last_id=${lastMessageId}`);
                const data = await response.json();

                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        displayMessage(msg);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                    scrollToBottom();
                }
            } catch (error) {
                console.error('메시지 확인 실패:', error);
            } finally {
                isLoading = false;
            }
        }

        // 메시지 전송
        async function sendMessage(message) {
            const formData = new FormData();
            formData.append('message', message);

            try {
                const response = await fetch('api.php?action=send', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (!data.success) {
                    alert(data.error || '메시지 전송 실패');
                }
            } catch (error) {
                console.error('메시지 전송 실패:', error);
                alert('메시지 전송 중 오류가 발생했습니다.');
            }
        }

        // 메시지 표시
        function displayMessage(msg) {
            const messagesDiv = document.getElementById('messages');
            const isOwn = msg.username === currentUsername;

            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isOwn ? 'own' : ''}`;

            const time = new Date(msg.created_at).toLocaleTimeString('ko-KR', {
                hour: '2-digit',
                minute: '2-digit'
            });

            messageDiv.innerHTML = `
                <div class="username">${escapeHtml(msg.username)}</div>
                <div class="bubble">${escapeHtml(msg.message)}</div>
                <div class="time">${time}</div>
            `;

            messagesDiv.appendChild(messageDiv);
        }

        // 온라인 사용자 업데이트
        async function updateOnlineUsers() {
            try {
                // 자신의 온라인 상태 업데이트
                await fetch('api.php?action=update_online', { method: 'POST' });

                // 온라인 사용자 목록 가져오기
                const response = await fetch('api.php?action=online_users');
                const data = await response.json();

                if (data.success) {
                    const userListDiv = document.getElementById('userList');
                    userListDiv.innerHTML = data.users.map(user =>
                        `<div class="user-item">${escapeHtml(user)}</div>`
                    ).join('');
                }
            } catch (error) {
                console.error('온라인 사용자 업데이트 실패:', error);
            }
        }

        // HTML 이스케이프
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // 스크롤 맨 아래로
        function scrollToBottom() {
            const messagesDiv = document.getElementById('messages');
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // 로그아웃
        function logout() {
            if (confirm('로그아웃하시겠습니까?')) {
                window.location.href = 'logout.php';
            }
        }

        // 폼 제출 처리
        document.getElementById('messageForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message) return;

            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;

            await sendMessage(message);
            input.value = '';

            sendBtn.disabled = false;
            input.focus();

            // 즉시 새 메시지 확인
            setTimeout(checkNewMessages, 100);
        });

        // 초기화
        loadMessages();
        updateOnlineUsers();

        // 주기적 업데이트
        setInterval(checkNewMessages, 1000); // 1초마다 새 메시지 확인
        setInterval(updateOnlineUsers, 5000); // 5초마다 온라인 사용자 업데이트

        // 페이지 언로드 시 온라인 상태 제거
        window.addEventListener('beforeunload', () => {
            navigator.sendBeacon('api.php?action=offline');
        });
    </script>
</body>
</html>
