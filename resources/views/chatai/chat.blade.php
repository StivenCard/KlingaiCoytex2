<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            font-size: 26px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
            z-index: 1000;
            transition: all 0.3s ease-in-out;
        }
        #chat-toggle:hover {
            background: #2563eb;
            transform: scale(1.1);
        }

        /* 📦 Ventana de chat */
        #chat-box {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 600px;
            height: 600px;
            max-width: 95%;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 14px;
            display: none;
            flex-direction: column;
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
            z-index: 999;
            overflow: hidden;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(10px);}
            to {opacity: 1; transform: translateY(0);}
        }

        /* 🧾 Header */
        #chat-header {
            background: #3b82f6;
            color: white;
            padding: 14px;
            font-weight: bold;
            text-align: center;
            font-size: 16px;
        }

        /* 📜 Mensajes */
        #chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            font-size: 15px;
            line-height: 1.5;
            background: #f9fafb;
        }

        .message {
            margin: 10px 0;
            padding: 12px 14px;
            border-radius: 16px;
            max-width: 80%;
            clear: both;
            position: relative;
            word-wrap: break-word;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }

        .message .icon {
            font-size: 18px;
            margin-bottom: auto;
            color: #555;
        }

        .message small {
            display: block;
            font-size: 11px;
            margin-top: 4px;
            opacity: 0.7;
        }

        .user-message .icon {
            color: #fff; /* ícono blanco sobre fondo azul */
        }

        .assistant-message .icon {
            color: #3b82f6; /* ícono azul sobre fondo claro */
        }

        .user-message {
            background: #3b82f6;
            color: white;
            float: right;
            border-bottom-right-radius: 4px;
            flex-direction: row-reverse; /* icono a la derecha */
        }

        .assistant-message {
            background: #ffffff;
            border: 1px solid #eee;
            float: left;
            border-bottom-left-radius: 4px;
            flex-direction: row; /* icono a la izquierda */
        }

        /* ✍️ Input */
        #chat-input {
            display: flex;
            border-top: 1px solid #ddd;
            background: white;
        }

        #message {
            flex: 1;
            border: none;
            padding: 12px;
            font-size: 15px;
            outline: none;
        }

        #send-btn {
            background: #3b82f6;
            border: none;
            color: white;
            padding: 0 20px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 0;
        }

        #send-btn:hover {
            background: #2563eb;
        }

        /* "Escribiendo..." */
        #typing {
            font-size: 13px;
            color: #666;
            font-style: italic;
            margin: 10px;
            display: none;
        }

        /* Contenido principal de la página */
        .main-content {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .hero-section {
            text-align: center;
            padding: 50px 20px;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .hero-description {
            font-size: 1rem;
            opacity: 0.8;
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <!-- Contenido principal de la página -->
    <div class="main-content">
        <div class="hero-section">
            <h1 class="hero-title">💬 Asistente Empresarial AI</h1>
            <p class="hero-subtitle">Tu compañero inteligente para consultas empresariales</p>
            <p class="hero-description">
                Haz clic en el botón de chat flotante para comenzar una conversación con nuestro asistente AI.
                Obtén respuestas inmediatas sobre temas empresariales, estrategias y mucho más.
            </p>
        </div>
    </div>

    <!-- 🔘 Botón flotante -->
    <button id="chat-toggle" aria-label="Abrir chat"><i class="fas fa-comment-dots"></i></button>

    <!-- 📦 Ventana de chat -->
    <div id="chat-box">
        <div id="chat-header">💬 Asistente Empresarial</div>
        <div id="chat-messages">
            <!-- Mensaje de bienvenida -->
            <div class="message assistant-message">
                <span class="icon"><i class="fas fa-robot"></i></span>
                <div>
                    ¡Hola! 👋 Soy tu <b>SIO BOT</b>. Tu asistente virtual inteligente estoy aquí para ayudarte con cualquier consulta.
                    <small id="welcome-time"></small>
                </div>
            </div>

            @foreach ($historial as $registro)
                <div class="message user-message">
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <div>
                        {!!$registro->user_message!!}
                        <small>{{ $registro->created_at->format('H:i') }}</small>
                    </div>
                </div>

                <div class="message assistant-message">
                    <span class="icon"><i class="fas fa-robot"></i></span>
                    <div>
                        {!!$registro->ai_response!!}
                        <small>{{ $registro->created_at->format('H:i') }}</small>
                    </div>
                </div>
            @endforeach
        </div>
        <div id="typing">El asistente está escribiendo...</div>
        <div id="chat-input">
            <input type="text" id="message" placeholder="Escribe tu mensaje..." aria-label="Mensaje">
            <button id="send-btn">➤</button>
        </div>
    </div>

    <script>
        // Configurar CSRF token para todas las peticiones AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const chatToggle = document.getElementById("chat-toggle");
        const chatBox = document.getElementById("chat-box");
        const sendBtn = document.getElementById("send-btn");
        const messageInput = document.getElementById("message");
        const chatMessages = document.getElementById("chat-messages");
        const typing = document.getElementById("typing");

        // Mostrar hora de bienvenida
        document.getElementById('welcome-time').textContent = new Date().toLocaleTimeString([], {hour: "2-digit", minute: "2-digit"});

        // Mostrar/Ocultar chat
        chatToggle.addEventListener("click", () => {
            const isVisible = chatBox.style.display === "flex";
            chatBox.style.display = isVisible ? "none" : "flex";
            if (!isVisible) {
                messageInput.focus();
                // 🟢 CAMBIO: Desplazar el chat al final al abrirlo
                chatMessages.scrollTo({top: chatMessages.scrollHeight, behavior: "smooth"});
            }
        });

        // Enviar mensaje con Enter
        messageInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        sendBtn.addEventListener("click", sendMessage);

        // Función para agregar mensajes
        function appendMessage(text, type) {
            const div = document.createElement("div");
            div.classList.add("message", type === "user" ? "user-message" : "assistant-message");

            // Crear ícono con Font Awesome
            const icon = document.createElement("span");
            icon.classList.add("icon");
            icon.innerHTML = type === "user"
                ? '<i class="fas fa-user"></i>'
                : '<i class="fas fa-robot"></i>';

            // Crear contenedor de texto
            const content = document.createElement("div");
            content.innerHTML = text.replace(/\n/g, "<br>").replace(/- (.*)/g, "• $1");

            // Timestamp
            const time = document.createElement("small");
            time.textContent = new Date().toLocaleTimeString([], {hour: "2-digit", minute: "2-digit"});
            content.appendChild(time);

            // Juntamos ícono + texto
            div.appendChild(icon);
            div.appendChild(content);

            chatMessages.appendChild(div);
            chatMessages.scrollTo({top: chatMessages.scrollHeight, behavior: "smooth"});
        }

        // Función para enviar mensaje al servidor
        async function sendMessage() {
            const userText = messageInput.value.trim();
            if (!userText) return;

            // Deshabilitar input mientras se procesa
            messageInput.disabled = true;
            sendBtn.disabled = true;

            appendMessage(userText, "user");
            messageInput.value = "";
            typing.style.display = "block"; // Mostrar indicador

            try {
                const response = await fetch("{{ route('chatai.send') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ message: userText })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                typing.style.display = "none";

                if (data.error) {
                    appendMessage("⚠️ " + data.error, "assistant");
                } else {
                    appendMessage(data.assistant, "assistant");
                }

            } catch (error) {
                typing.style.display = "none";
                console.error('Error:', error);
                appendMessage("⚠️ Error al conectar con el servidor. Por favor, inténtalo de nuevo.", "assistant");
            } finally {
                // Rehabilitar input
                messageInput.disabled = false;
                sendBtn.disabled = false;
                messageInput.focus();
            }
        }

        // Focus automático cuando se abre el chat
        document.addEventListener('DOMContentLoaded', function() {
            // Si el chat está visible al cargar la página, enfocar el input y desplazarlo
            if (chatBox.style.display === "flex") {
                messageInput.focus();
                //Desplazar el chat al final al cargar la página
                chatMessages.scrollTo({top: chatMessages.scrollHeight, behavior: "smooth"});
            }
        });
    </script>
</body>
</html>
