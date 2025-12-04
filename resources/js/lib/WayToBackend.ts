import { useAlertsStore } from "@/store/alertStore";
import { useUserStore } from "@/store/userStore";
import { ApiError, ValidationError, UnauthorizedError, ForbiddenError, ServerError } from "./ApiErrors";
import { UserLogin, UserRegister } from "@/types";

class WayToBackend {
    readonly #endpoint: string = import.meta.env.VITE_APP_URL || "http://localhost:8000";
    readonly #addAlert = useAlertsStore.getState().addAlert;
    readonly #getUser = () => useUserStore.getState().user;
    readonly #setUser = useUserStore.getState().setUser;
    readonly #clearUser = useUserStore.getState().clearUser;

    #getAccessToken(): string | null {
        let bearerToken = '';
        const user = this.#getUser();
        if (user && user?.access_token) {
            bearerToken = `Bearer ${user.access_token}`;
        } else {
            // Пытаемся получить токен из localStorage
            const accessToken = localStorage.getItem('access_token');
            if (accessToken) {
                bearerToken = `Bearer ${accessToken}`;
            }
        }
        return bearerToken;
    }

    async #refreshToken(): Promise<void> {
        try {
            const user = this.#getUser();
            const refreshToken: string  = (user) ? user?.refresh_token : '';
            const url = new URL(`${this.#endpoint}/api/auth/refresh`);
            url.searchParams.append('refresh-token', refreshToken);
            // Отправляем запрос на обновление токена
            const response = await fetch(url.toString(), {
                method: 'POST',
                headers: {
                    "Accept": "application/json",
                },
                credentials: 'include',
            });

            if (!response.ok) {
                console.error('Failed to refresh token:', response.status, response.statusText);
            }
            const resp = await response.json()
            // Сохраняем обновленные токены в localStorage
            if (resp.data.data.access_token) {
                localStorage.setItem('access_token', resp.data.data.access_token);
                localStorage.setItem('access_token_expires_at', resp.data.data.access_token_expires_at);
                localStorage.setItem('refresh_token', resp.data.data.refresh_token);
                localStorage.setItem('refresh_token_expires_at', resp.data.data.refresh_token_expires_at);
            }
            this.#setUser(resp.data.data)
        } catch (error) {
            console.error('Error refreshing token:', error);
            this.#clearUser()
            // Добавляем уведомление об ошибке
            this.#addAlert({ message: "Ошибка обновления токена безопасности. Пожалуйста, перезагрузите страницу." });
        }
    }

    async registerUser(data: UserRegister) {
        try {
            const result = await this.#fetchData("auth/register", data);
            // Сохраняем токены в localStorage
            if (result.data.data.access_token) {
                localStorage.setItem('access_token', result.data.data.access_token);
                localStorage.setItem('access_token_expires_at', result.data.data.access_token_expires_at);
                localStorage.setItem('refresh_token', result.data.data.refresh_token);
                localStorage.setItem('refresh_token_expires_at', result.data.data.refresh_token_expires_at);
            }
            // Добавляем успешное уведомление
            this.#addAlert({ message: "Вы успешно зарегистрировались!" });
            return { success: true, data: result };
        } catch (error) {
            // Добавляем уведомление об ошибке
            if (!(error instanceof ValidationError)) {
                // Проверяем тип ошибки для более точного сообщения
                if ((error as Error).name === 'AbortError') {
                    this.#addAlert({ message: "Превышено время ожидания ответа от сервера. Пожалуйста, попробуйте позже." });
                } else if ((error as Error).message && (error as Error).message.includes('Failed to fetch')) {
                    this.#addAlert({ message: "Ошибка сети. Пожалуйста, проверьте подключение к интернету." });
                } else if ((error as Error).message &&
                         ((error as Error).message.includes('CORS') ||
                          (error as Error).message.includes('Access to fetch') ||
                          (error as Error).message.includes('has been blocked'))) {
                    this.#addAlert({ message: "Ошибка безопасности. Пожалуйста, свяжитесь с администратором." });
                } else {
                    this.#addAlert({ message: "Ошибка при регистрации. Пожалуйста, попробуйте еще раз." });
                }
            }
            throw error;
        }
    }

    async logoutUser() {
        try {
            const result = await this.#fetchData("auth/logout")
            // Очищаем токены из localStorage
            localStorage.removeItem('access_token');
            localStorage.removeItem('access_token_expires_at');
            localStorage.removeItem('refresh_token');
            localStorage.removeItem('refresh_token_expires_at');
            return result;
        } catch (error) {
            console.error(error);
            throw error;
        }
    }

    async loginUser(data: UserLogin) {
        try {
            const result = await this.#fetchData("auth/login", data);
            // Сохраняем токены в localStorage
            if (result.data.data.access_token) {
                localStorage.setItem('access_token', result.data.data.access_token);
                localStorage.setItem('access_token_expires_at', result.data.data.access_token_expires_at);
                localStorage.setItem('refresh_token', result.data.data.refresh_token);
                localStorage.setItem('refresh_token_expires_at', result.data.data.refresh_token_expires_at);
            }
            // Добавляем успешное уведомление
            this.#addAlert({ message: "Вы успешно вошли в систему!" });
            return { success: true, data: result };
        } catch (error) {
            // Добавляем уведомление об ошибке
            if (!(error instanceof ValidationError)) {
                // Проверяем тип ошибки для более точного сообщения
                if ((error as Error).name === 'AbortError') {
                    this.#addAlert({ message: "Превышено время ожидания ответа от сервера. Пожалуйста, попробуйте позже." });
                } else if ((error as Error).message && (error as Error).message.includes('Failed to fetch')) {
                    this.#addAlert({ message: "Ошибка сети. Пожалуйста, проверьте подключение к интернету." });
                } else if ((error as Error).message &&
                         ((error as Error).message.includes('CORS') ||
                          (error as Error).message.includes('Access to fetch') ||
                          (error as Error).message.includes('has been blocked'))) {
                    this.#addAlert({ message: "Ошибка безопасности. Пожалуйста, свяжитесь с администратором." });
                } else {
                    this.#addAlert({ message: "Ошибка при входе. Пожалуйста, проверьте email и пароль." });
                }
            } else {
                this.#addAlert({message: error.message})
            }
            throw error;
        }
    }

    async #fetchData(url: string, data: any = {}) {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // Таймаут 10 секунд

            const method = this.#detectMethod(url);

            const headers: Record<string, string> = {
                "Content-Type": "application/json",
                "Accept": "application/json",
            };


            const accessToken = this.#getAccessToken();
            if (accessToken) {
                headers["Authorization"] = accessToken;
            }

            let response = await fetch(`${this.#endpoint}/api/${url}`, {
                method,
                headers,
                body: JSON.stringify(data),
                redirect: 'follow',
                signal: controller.signal
            });

            // Если получили 401 ошибку (истек токен), обновляем токен и повторяем запрос
            if (response.status === 401) {
                await this.#refreshToken();

                // Повторяем запрос с новым токеном
                response = await fetch(`${this.#endpoint}/api/${url}`, {
                    method: method,
                    headers: headers,
                    body: JSON.stringify(data),
                    redirect: 'follow',
                    credentials: 'include',
                    signal: controller.signal
                });
            }

            clearTimeout(timeoutId);

            // Проверяем, является ли ответ редиректом
            if (response.status >= 300 && response.status < 400) {
                // Для редиректов (особенно 302) считаем это ошибкой аутентификации
                throw new UnauthorizedError("Неправильные учетные данные");
            }

            if (response.ok) {
                try {
                    return await response.json();
                } catch (parseError) {
                    // Если не удалось распарсить JSON, возвращаем текст ответа
                    const text = await response.text();
                    console.error(text)
                    this.#addAlert({ message: "Получен некорректный ответ от сервера. Пожалуйста, попробуйте позже." });
                    throw new ApiError("Некорректный ответ от сервера", response.status);
                }
            } else {
                // Обработка различных типов ошибок
                let errorData;
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    try {
                        errorData = await response.json();
                    } catch (parseError) {
                        // Если не удалось распарсить JSON, используем текст ответа
                        const text = await response.text();
                        errorData = { message: text || response.statusText };
                    }
                } else {
                    // Если ответ не JSON, используем текст ответа
                    const text = await response.text();
                    errorData = { message: text || response.statusText };
                }

                switch (response.status) {
                    case 422:
                        const errors = errorData?.messages ?? [];
                        throw new ValidationError(
                            errorData.message || 'Ошибка валидации данных',
                            errors
                        );
                    case 401:
                        throw new UnauthorizedError(
                            errorData.message || "Неправильные учетные данные. Пожалуйста, проверьте email и пароль."
                        );
                    case 403:
                        throw new ForbiddenError(
                            errorData.message || "Доступ запрещен. У вас нет прав для выполнения этого действия."
                        );
                    case 500:
                        throw new ServerError(
                            errorData.message || "Внутренняя ошибка сервера"
                        );
                    default:
                        throw new ApiError(
                            errorData.message || response.statusText || "Неизвестная ошибка",
                            response.status,
                            errorData
                        );
                }
            }
        } catch (error) {
            // Если это наша кастомная ошибка, показываем сообщение
            if (error instanceof ValidationError) {
                if (error.errors) {
                    for(const keyError in error.errors) {
                        this.#addAlert({
                            message: error.errors[keyError][0], // Берем первую ошибку для каждого поля
                        });
                    }
                } else {
                    this.#addAlert({ message: error.message });
                }
            } else if (error instanceof ApiError) {
                this.#addAlert({ message: error.message });
            } else if ((error as Error).name === 'AbortError') {
                // Обработка таймаута
                this.#addAlert({ message: "Превышено время ожидания ответа от сервера. Пожалуйста, попробуйте позже." });
            } else if ((error as Error).message && (error as Error).message.includes('Failed to fetch')) {
                // Обработка ошибок сети
                this.#addAlert({ message: "Ошибка сети. Пожалуйста, проверьте подключение к интернету." });
            } else if ((error as Error).message &&
                       ((error as Error).message.includes('CORS') ||
                        (error as Error).message.includes('Access to fetch') ||
                        (error as Error).message.includes('has been blocked'))) {
                // Обработка ошибок CORS
                this.#addAlert({ message: "Ошибка безопасности. Пожалуйста, свяжитесь с администратором." });
            } else {
                // Для других ошибок показываем общее сообщение
                this.#addAlert({ message: "Ошибка соединения с сервером" });
            }
            throw error;
        }
    }

    #detectMethod(url: string) {
        switch (url) {
            case "auth/register":
            case "auth/login":
            case "auth/logout":
                return "POST";
            default:
                throw new Error("Method not found");
        }
    }

    // Метод для проверки статуса аутентификации
    async checkAuthStatus(): Promise<boolean> {
        try {
            // Проверяем наличие токена в localStorage
            const accessToken = localStorage.getItem('access_token');
            if (!accessToken) {
                return false;
            }

            const response = await fetch(`${this.#endpoint}/api/auth/user`, {
                method: 'GET',
                headers: {
                    "Accept": "application/json",
                    "Authorization": `Bearer ${accessToken}`,
                },
                credentials: 'include',
            });

            // Если получили 401, значит пользователь не авторизован
            if (response.status === 401) {
                // Очищаем токены из localStorage
                localStorage.removeItem('access_token');
                localStorage.removeItem('access_token_expires_at');
                localStorage.removeItem('refresh_token');
                localStorage.removeItem('refresh_token_expires_at');
                return false;
            }

            return response.ok;
        } catch (error) {
            console.error('Error checking auth status:', error);
            return false;
        }
    }
}

export default new WayToBackend();
