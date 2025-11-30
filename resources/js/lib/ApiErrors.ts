export class ApiError extends Error {
    constructor(message: string, public status?: number, public data?: any) {
        super(message);
        this.name = 'ApiError';
    }
}

export class ValidationError extends ApiError {
    constructor(message: string, public errors: Record<string, string[]>) {
        super(message, 422, { errors });
        this.name = 'ValidationError';
    }
}

export class UnauthorizedError extends ApiError {
    constructor(message: string = "Неправильные учетные данные") {
        super(message, 401);
        this.name = 'UnauthorizedError';
    }
}

export class ForbiddenError extends ApiError {
    constructor(message: string = "Доступ запрещен") {
        super(message, 403);
        this.name = 'ForbiddenError';
    }
}

export class ServerError extends ApiError {
    constructor(message: string = "Внутренняя ошибка сервера") {
        super(message, 500);
        this.name = 'ServerError';
    }
}
