const BASE_URL = "";

/**
 * Função genérica para realizar requisições à API.
 * Lida com autorização e tratamento de erros de forma centralizada.
 * @param {string} endpoint - A parte da URL após o endereço base (ex: "users/me").
 * @param {object} options - As opções do fetch (method, body, etc.).
 * @returns {Promise<any>} O corpo da resposta em JSON.
 */
async function apiFetch(endpoint, options = {}) {
  const token = localStorage.getItem("jwtToken");

  const headers = {
    "Content-Type": "application/json",
    ...options.headers,
  };

  if (token) {
    headers.Authorization = `Bearer ${token}`;
  } else {
    window.location.href = "/login/index.html";
    throw new Error("Token de autenticação não encontrado.");
  }

  const response = await fetch(`${BASE_URL}${endpoint}`, {
    ...options,
    headers,
  });

  const responseBody = await response.json();

  if (!response.ok) {
    if (response.status === 401) {
      console.log(
        "Token inválido ou expirado (Erro 401). Limpando e redirecionando..."
      );
      localStorage.removeItem("jwtToken");
      window.location.href = "../login/index.html";
    }

    const errorBody = await response
      .json()
      .catch(() => ({ message: "Erro desconhecido." }));
    throw new Error(errorBody.message || "Ocorreu um erro na requisição.");
  }

  return responseBody;
}

export function getData(endpoint) {
  return apiFetch(endpoint, { method: "GET" });
}

export function postData(endpoint, body) {
  return apiFetch(endpoint, {
    method: "POST",
    body: JSON.stringify(body),
  });
}

export async function publicPostData(endpoint, body) {
  const response = await fetch(`${BASE_URL}${endpoint}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(body),
  });

  const responseBody = await response.json();

  if (!response.ok) {
    throw new Error(responseBody.message || "Ocorreu um erro na requisição.");
  }

  return responseBody;
}
