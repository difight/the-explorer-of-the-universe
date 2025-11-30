export interface User {
    id: number;
    name: string;
    email: string;
}
export interface UserData {
    access_token: string;
    access_token_expires_at: string;
    refresh_token: string;
    refresh_token_expires_at: string;
    user: User
}
export interface UserStore {
    user: UserData | null;
    setUser: (user: UserData) => void;
}

export interface AlertStore {
    alerts: AlertMessage [];
    addAlert: (alert: AlertMessage) => void;
    clearAlerts: () => void;
}

export interface AlertMessage {
    message: string;
}
