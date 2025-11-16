export interface User {
    id: number;
    name: string;
    email: string;
}
export interface UserStore {
    user: User | null;
    setUser: (user: User) => void;
}

export interface AlertStore {
    alerts: AlertMessage [];
    addAlert: (alert: AlertMessage) => void;
    clearAlerts: () => void;
}

export interface AlertMessage {
    message: string;
}
