"use client"

import { useEffect } from "react";
import { useToast } from "@chakra-ui/react"
import { useAlertsStore } from "@/store/alertStore";

export const Toaster = () => {
  const alerts = useAlertsStore((state) => state.alerts);
  const clearAlerts = useAlertsStore((state) => state.clearAlerts);
  const toast = useToast();

  // Добавляем уведомления из нашего store в toaster
  useEffect(() => {
    alerts.forEach((alert) => {
      // Определяем тип уведомления по содержимому сообщения
      const isSuccess = alert.message.includes("успешно") || alert.message.includes("Success") || alert.message.includes("success");
      const status = isSuccess ? "success" : "error";
      const title = isSuccess ? "Успешно" : "Ошибка";

      toast({
        title: title,
        description: alert.message,
        status: status,
        duration: 5000,
        isClosable: true,
      });
    });

    // Очищаем alerts после отображения
    if (alerts.length > 0) {
      clearAlerts();
    }
  }, [alerts]);

  return null;
}
