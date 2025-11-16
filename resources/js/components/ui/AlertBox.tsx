import { useAlertsStore } from "@/store/alertStore";
import {
    AlertDialog,
    AlertDialogBody,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogContent,
    AlertDialogOverlay,
    AlertDialogCloseButton,
    Button,
    Alert,
    AlertIcon,
    AlertTitle,
    AlertDescription
} from "@chakra-ui/react";
import { useDisclosure } from "@chakra-ui/react";
import { useRef, useEffect } from "react";

const AlertBox = () => {
    const alerts = useAlertsStore((state) => state.alerts);
    const clearAlerts = useAlertsStore((state) => state.clearAlerts);
    const { isOpen, onOpen, onClose } = useDisclosure()
    const cancelRef = useRef<HTMLButtonElement>(null)
    useEffect(() => {
        if (alerts.length > 0) {
            onOpen()
        } else {
            onClose()
        }
    }, [alerts])

    const onHandleClose = () => {
        onClose()
        clearAlerts()
    }

    return (
        <>
            <AlertDialog
                isOpen={isOpen}
                leastDestructiveRef={cancelRef}
                onClose={onClose}
            >
                <AlertDialogOverlay>
                    <AlertDialogContent>
                        <AlertDialogHeader fontSize='lg' fontWeight='bold'>
                            Произошли ошибки
                        </AlertDialogHeader>
                        <AlertDialogBody>
                            {alerts.map((alert, index) => (
                                <Alert key={index} status="error">
                                    <AlertIcon />
                                    <AlertTitle mr={2}>Error!</AlertTitle>
                                    <AlertDescription>{alert.message}</AlertDescription>
                                </Alert>
                            ))}
                        </AlertDialogBody>
                        <AlertDialogFooter>
                            <Button ref={cancelRef} onClick={onHandleClose}>
                                Закрыть
                            </Button>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialogOverlay>
            </AlertDialog>
        </>

    );
}
export default AlertBox;
