import { create } from 'zustand';
import { AlertStore, AlertMessage } from '@/types';
export const useAlertsStore = create<AlertStore>((set, get) => ({
    alerts: [],
    addAlert: (alert: AlertMessage) => set({ alerts: [...get().alerts, alert] }),
    clearAlerts: () => set({ alerts: [] }),
}));

const alertStore = useAlertsStore;
export default alertStore;
