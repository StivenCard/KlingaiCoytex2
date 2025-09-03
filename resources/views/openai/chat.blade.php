<!-- resources/views/openai/chat.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Chat Empresarial</title>
    <style>
        /* 🔘 Botón flotante */
        #chat-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 28px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s;
        }
        #chat-toggle:hover {
            background: #5f8def;
            transform: scale(1.05);
        }

        /* 📦 Ventana de chat */
        #chat-box {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 500px;
            height: 600px;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 12px;
            display: none;
            flex-direction: column;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            z-index: 999;
        }

        /* 🧾 Header */
        #chat-header {
            background: #3b82f6;
            color: white;
            padding: 12px;
            border-radius: 12px 12px 0 0;
            font-weight: bold;
        }

        /* 📜 Mensajes */
        #chat-messages {
            flex: 1;
            padding: 12px;
            overflow-y: auto;
            font-size: 14px;
            line-height: 1.4;
        }
        .message {
            margin: 8px 0;
            padding: 10px;
            border-radius: 8px;
            max-width: 80%;
            clear: both;
        }
        .user-message {
            background: #3b82f6;
            color: white;
            float: right;
        }
        .assistant-message {
            background: #f3f4f6;
            float: left;
        }

        /* ✍️ Input */
        #chat-input {
            display: flex;
            border-top: 1px solid #ddd;
        }
        #message {
            flex: 1;
            border: none;
            padding: 10px;
            border-radius: 0 0 0 12px;
            outline: none;
        }
        #send-btn {
            background: #3b82f6;
            border: none;
            color: white;
            padding: 12px 16px;
            cursor: pointer;
            border-radius: 0 0 12px 0;
        }
        #send-btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>

    <!-- 🔘 Botón flotante -->
    <button id="chat-toggle"><i class="fas fa-comment-dots"></i></button>

    <!-- 📦 Ventana de chat -->
    <div id="chat-box">
        <div id="chat-header">Asistente Empresarial</div>
        <div id="chat-messages"></div>
        <div id="chat-input">
            <input type="text" id="message" placeholder="Escribe tu mensaje..." />
            <button id="send-btn">➤</button>
        </div>
    </div>

    <script>
        const chatToggle = document.getElementById("chat-toggle");
        const chatBox = document.getElementById("chat-box");
        const sendBtn = document.getElementById("send-btn");
        const messageInput = document.getElementById("message");
        const chatMessages = document.getElementById("chat-messages");

        // Mostrar/Ocultar chat
        chatToggle.addEventListener("click", () => {
            chatBox.style.display = chatBox.style.display === "flex" ? "none" : "flex";
        });

        // Enviar mensaje
        sendBtn.addEventListener("click", sendMessage);
        messageInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") sendMessage();
        });

        function appendMessage(text, type) {
            const div = document.createElement("div");
            div.classList.add("message", type === "user" ? "user-message" : "assistant-message");
            div.innerText = text;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        async function sendMessage() {
            const userText = messageInput.value.trim();
            if (!userText) return;
            appendMessage(userText, "user");
            messageInput.value = "";

            // Enviar al backend
            const response = await fetch("{{ route('openai.send') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ message: userText })
            });

            const data = await response.json();
            appendMessage(data.assistant, "assistant");
        }
    </script>
</body>
</html>
