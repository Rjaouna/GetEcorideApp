import axios from "axios";

const http = axios.create({
    baseURL: "http://localhost:8000", // adapte selon ton host
    timeout: 10000,
    headers: {
        Accept: "application/json",
    },
});

// (Optionnel) si tu utilises JWT (LexikJWT), ajoute le token
http.interceptors.request.use((config) => {
    const token = localStorage.getItem("auth_token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default http;
