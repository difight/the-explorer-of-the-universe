export interface User {
    id: number;
    name: string;
    email: string;
    created_at?: string;
}
export interface UserData {
    access_token: string;
    access_token_expires_at: string;
    refresh_token: string;
    refresh_token_expires_at: string;
    user: User
}
export interface UserLogin {
    email: string;
    password: string;
}

export interface UserRegister {
    email: string;
    name: string;
    password: string;
    password_confirmation: string;
}
export interface UserStore {
    user: UserData | null;
    setUser: (user: UserData) => void;
    clearUser: () => void;
}

export interface AlertStore {
    alerts: AlertMessage [];
    addAlert: (alert: AlertMessage) => void;
    clearAlerts: () => void;
}

export interface AlertMessage {
    message: string;
}
