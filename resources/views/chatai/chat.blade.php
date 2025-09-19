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
    /* Estilos generales */
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background: #f5f5f5;
        color: #1f1f1f;
    }

    /* Botón flotante */
    #chat-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #4b4b4b;
        color: white;
        border: none;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        font-size: 26px;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
        z-index: 1000;
        transition: all 0.3s ease-in-out;
    }

    #chat-toggle:hover {
        background: #7a7a7a;
        transform: scale(0.95);
    }

    /* Ventana de chat */
    #chat-box {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 600px;
        height: 600px;
        max-width: 95%;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 14px;
        display: none;
        flex-direction: column;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
        z-index: 999;
        overflow: hidden;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Header */
    #chat-header {
        background: #4b4b4b;
        color: white;
        padding: 14px;
        font-weight: bold;
        text-align: center;
        font-size: 16px;
    }

    /* Mensajes */
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
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .message .icon {
        font-size: 18px;
        margin-top: 2px;
        min-width: 20px;
    }

    .message .content {
        flex: 1;
    }

    .message small {
        display: block;
        font-size: 11px;
        margin-top: 6px;
        opacity: 0.6;
    }

    .user-message {
        background: #6b7280;
        color: white;
        float: right;
        border-bottom-right-radius: 4px;
        flex-direction: row-reverse;
    }

    .user-message .icon {
        color: #fff;
    }

    .assistant-message {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        float: left;
        border-bottom-left-radius: 4px;
        color: #1f2937;
    }

    .assistant-message .icon {
        color: #4b5563;
    }

    /* Mensaje de sistema (datos BD) */
    .system-data {
        background: #e5e7eb;
        border: 1px solid #9ca3af;
        color: #374151;
    }

    .system-data .icon {
        color: #4b5563;
    }

    /* Input */
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
        background: #303030;
        border: none;
        color: white;
        padding: 0 20px;
        font-size: 18px;
        cursor: pointer;
        border-radius: 0;
        transition: background 0.2s;
    }

    #send-btn:hover {
        background: #606060;
    }

    #send-btn:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }

    /* "Escribiendo..." */
    #typing {
        font-size: 13px;
        color: #6b7280;
        font-style: italic;
        margin: 10px;
        display: none;
    }

    /* Estilos para tablas de datos */
    .data-table {
        max-width: 100%;
        overflow-x: auto;
        margin: 10px 0;
    }

    .data-table table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .data-table th,
    .data-table td {
        padding: 6px 8px;
        text-align: left;
        border: 1px solid #d1d5db;
    }

    .data-table th {
        background: #e5e7eb;
        font-weight: bold;
    }

    /* Contenido principal */
    .main-content {
        min-height: 100vh;
        background: linear-gradient(135deg, #f9f9f9 0%, #c7c7c7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #111;
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
        opacity: 0.85;
        margin-bottom: 2rem;
    }

    .hero-description {
        font-size: 1rem;
        opacity: 0.75;
        max-width: 600px;
        margin: 0 auto;
    }
</style>
</head>
<body>
    <div class="main-content">
        <div class="hero-section">
            <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
                <img src="{{ asset('images/logo_sio.png') }}" alt="Logo de SIO" style="width: 250px; height: auto;">
            </div>
            <h1 class="hero-title">Asistente Empresarial AI</h1>
            <p class="hero-subtitle">Tu compañero inteligente para consultas empresariales</p>
            <p class="hero-description">
                Haz clic en el botón de chat flotante para comenzar una conversación con nuestro asistente AI.
                Obtén respuestas inmediatas sobre datos de la base de datos, estadísticas y mucho más.
            </p>
        </div>
    </div>
    <button id="chat-toggle" aria-label="Abrir chat"><i class="fas fa-comment-dots"></i></button>
    <div id="chat-box">
        <div id="chat-header">💬 Asistente Empresarial</div>
        <div id="chat-messages">
            <div class="message assistant-message">
                <span class="icon"><i class="fas fa-robot"></i></span>
                <div class="content">
                    ¡Hola! 👋 Soy tu <b>SIO BOT</b>. tu asistente virtual inteligente.
                    <br><br>
                    ¿En qué puedo ayudarte hoy?
                    <small id="welcome-time"></small>
                </div>
            </div>

            @foreach ($historial as $registro)
            <div class="message user-message">
                <span class="icon"><i class="fas fa-user"></i></span>
                <div class="content">
                    {!! $registro->user_message !!}
                    <small>{{ $registro->created_at->format('H:i') }}</small>
                </div>
            </div>

            <div class="message assistant-message">
                <span class="icon"><i class="fas fa-robot"></i></span>
                <div class="content">
                    {!! $registro->ai_response !!}
                    <small>{{ $registro->created_at->format('H:i') }}</small>
                </div>
            </div>
            @endforeach
        </div>
        <div id="typing">El asistente está analizando la información...</div>
        <div id="chat-input">
            <input type="text" id="message" placeholder="Ejemplo: ¿Cuáles son las estadísticas de uso?"
                aria-label="Mensaje">
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
        document.getElementById('welcome-time').textContent = new Date().toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit"
        });

        // Mostrar/Ocultar chat
        chatToggle.addEventListener("click", () => {
            const isVisible = chatBox.style.display === "flex";
            chatBox.style.display = isVisible ? "none" : "flex";
            if (!isVisible) {
                messageInput.focus();
                // Desplazar al final al abrir el chat
                chatMessages.scrollTo({
                    top: chatMessages.scrollHeight,
                    behavior: "smooth"
                });
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
        function appendMessage(text, type, isSystemData = false) {
            const div = document.createElement("div");
            div.classList.add("message", type === "user" ? "user-message" : "assistant-message");

            if (isSystemData) {
                div.classList.add("system-data");
            }

            // Crear ícono con Font Awesome
            const icon = document.createElement("span");
            icon.classList.add("icon");
            icon.innerHTML = type === "user" ?
                '<i class="fas fa-user"></i>' :
                isSystemData ?
                '<i class="fas fa-database"></i>' :
                '<i class="fas fa-robot"></i>';

            // Crear contenedor de contenido
            const content = document.createElement("div");
            content.classList.add("content");

            // Procesar el texto para mejor formato (Markdown básico a HTML)
            content.innerHTML = text;

            // Timestamp
            const time = document.createElement("small");
            time.textContent = new Date().toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit"
            });
            content.appendChild(time);

            // Juntamos ícono + contenido
            if (type === "user") {
                div.appendChild(content);
                div.appendChild(icon);
            } else {
                div.appendChild(icon);
                div.appendChild(content);
            }

            chatMessages.appendChild(div);
            chatMessages.scrollTo({
                top: chatMessages.scrollHeight,
                behavior: "smooth"
            });
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
            typing.style.display = "block";

            try {
                const response = await fetch("{{ route('chatai.send') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        message: userText
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                typing.style.display = "none";

                if (data.error) {
                    appendMessage("⚠️ " + data.error, "assistant");
                } else {
                    // Detectar si la respuesta contiene datos de BD
                    const containsDBData = data.assistant.includes('encontrado') ||
                        data.assistant.includes('historial') ||
                        data.assistant.includes('estadísticas') ||
                        data.assistant.includes('usuarios');

                    appendMessage(data.assistant, "assistant", containsDBData);
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

        // Ejemplos de preguntas sugeridas
        const exampleQuestions = [
            "Ejemplo: ¿Cuáles son los precios de los modelos disponibles?",
            "Ejemplo: Muéstrame los modelos virtuales del usuario CC",
            "Ejemplo: ¿Cuál es el precio del modelo kling-v1-6?",
            "Ejemplo: Consulta el estado de la tarea ABC123"
        ];

        // Agregar sugerencias al placeholder
        let placeholderIndex = 0;
        setInterval(() => {
            if (!messageInput.disabled && messageInput.value === "") {
                messageInput.placeholder = exampleQuestions[placeholderIndex];
                placeholderIndex = (placeholderIndex + 1) % exampleQuestions.length;
            }
        }, 3000);

        // Focus automático cuando se abre el chat
        document.addEventListener('DOMContentLoaded', function () {
            if (chatBox.style.display === "flex") {
                messageInput.focus();
                chatMessages.scrollTo({
                    top: chatMessages.scrollHeight,
                    behavior: "smooth"
                });
            }
        });
    </script>
</body>
</html>
