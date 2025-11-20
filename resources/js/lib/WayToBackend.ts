import { useAlertsStore } from "@/store/alertStore";
import { ApiError, ValidationError, UnauthorizedError, ForbiddenError, ServerError } from "./ApiErrors";

class WayToBackend {
    readonly #endpoint: string = import.meta.env.VITE_APP_URL || "http://localhost:8000";
    readonly #addAlert = useAlertsStore.getState().addAlert;

    async registerUser(data: any) {
        try {
            const result = await this.#fetchData("auth/register", data);
            // Добавляем успешное уведомление
            this.#addAlert({ message: "Вы успешно зарегистрировались!" });
            return { success: true, data: result };
        } catch (error) {
            throw error;
        }
    }

    async loginUser(data: any) {
        try {
            const result = await this.#fetchData("auth/login", data);
            // Добавляем успешное уведомление
            this.#addAlert({ message: "Вы успешно вошли в систему!" });
            return { success: true, data: result };
        } catch (error) {
            throw error;
        }
    }

    async #fetchData(url: string, data: any) {
        try {
            const method = this.#detectMethod(url);
            const response = await fetch(`${this.#endpoint}/api/${url}`, {
                method: method,
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });
            console.log('data', data)
            console.log('response', response)
            if (response.ok) {
                return await response.json();
            } else {
                // Обработка различных типов ошибок
                const errorData = await response.json();

                switch (response.status) {
                    case 422:
                        const errors = errorData?.messages ?? [];
                        throw new ValidationError(
                            errorData.message || 'Ошибка валидации данных',
                            errors
                        );
                    case 401:
                    case 302:
                        throw new UnauthorizedError(
                            errorData.message || "Неправильные учетные данные"
                        );
                    case 403:
                        throw new ForbiddenError(
                            errorData.message || "Доступ запрещен"
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
                return "POST";
            default:
                throw new Error("Method not found");
        }
    }
}

export default new WayToBackend();
