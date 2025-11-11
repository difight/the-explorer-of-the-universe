export interface User {
    id: number;
    name: string;
    email: string;
}
export interface UserStore {
  user: User | null;
  setUser: (user: User) => void;
}