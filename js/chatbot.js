const chatbotState = {
  isOpen: false,
  isLoading: false
};

function escapeChatbotHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function createChatbotMessage(content, sender) {
  const wrapper = document.createElement("div");
  wrapper.className = `chatbot-message chatbot-message-${sender}`;
  wrapper.innerHTML = `
    <div class="chatbot-message-bubble">
      ${escapeChatbotHtml(content).replace(/\n/g, "<br>")}
    </div>
  `;
  return wrapper;
}

function setChatbotLoading(form, statusLabel, isLoading) {
  chatbotState.isLoading = isLoading;

  const submitButton = form.querySelector('button[type="submit"]');
  const input = form.querySelector("input");

  if (submitButton) {
    submitButton.disabled = isLoading;
    submitButton.textContent = isLoading ? "Envoi..." : "Envoyer";
  }

  if (input) {
    input.disabled = isLoading;
  }

  if (statusLabel) {
    statusLabel.textContent = isLoading ? "L'assistant repond..." : "";
  }
}

async function sendChatbotMessage(message, messagesContainer, form, statusLabel) {
  const userMessage = createChatbotMessage(message, "user");
  messagesContainer.appendChild(userMessage);
  messagesContainer.scrollTop = messagesContainer.scrollHeight;

  setChatbotLoading(form, statusLabel, true);

  try {
    const response = await fetch("../php/chatbot.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json"
      },
      credentials: "same-origin",
      body: JSON.stringify({ message })
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(data.message || "Une erreur est survenue avec le chatbot.");
    }

    messagesContainer.appendChild(createChatbotMessage(data.reply || "Aucune reponse disponible.", "bot"));
  } catch (error) {
    messagesContainer.appendChild(
      createChatbotMessage(error.message || "Le chatbot est indisponible pour le moment.", "bot")
    );
  } finally {
    setChatbotLoading(form, statusLabel, false);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }
}

function initChatbot() {
  const chatbot = document.getElementById("chatbot-widget");
  if (!chatbot) {
    return;
  }

  const toggleButton = document.getElementById("chatbot-toggle");
  const panel = document.getElementById("chatbot-panel");
  const form = document.getElementById("chatbot-form");
  const input = document.getElementById("chatbot-input");
  const messagesContainer = document.getElementById("chatbot-messages");
  const statusLabel = document.getElementById("chatbot-status");

  if (!toggleButton || !panel || !form || !input || !messagesContainer) {
    return;
  }

  toggleButton.addEventListener("click", () => {
    chatbotState.isOpen = !chatbotState.isOpen;
    panel.classList.toggle("hidden", !chatbotState.isOpen);
    toggleButton.setAttribute("aria-expanded", chatbotState.isOpen ? "true" : "false");

    if (chatbotState.isOpen) {
      input.focus();
    }
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    if (chatbotState.isLoading) {
      return;
    }

    const message = input.value.trim();
    if (!message) {
      return;
    }

    input.value = "";
    await sendChatbotMessage(message, messagesContainer, form, statusLabel);
    input.focus();
  });
}

document.addEventListener("DOMContentLoaded", initChatbot);
