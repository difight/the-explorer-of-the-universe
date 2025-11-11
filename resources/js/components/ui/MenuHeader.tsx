// MenuHeader.tsx
import { 
    Stack,
    Button,
    Modal,
    ModalOverlay,
    ModalContent,
    ModalHeader,
    ModalFooter,
    ModalBody,
    ModalCloseButton,
    InputGroup,
    Input,
    InputRightElement,
    InputLeftElement
} from "@chakra-ui/react";
import { useState } from "react";
import { useUserStore } from '@/store/userStore';
import { CheckIcon } from "@chakra-ui/icons";

const MenuHeader = () => {
    const [showPasswordState, setShowPassword] = useState(false);
    const showPassword = () => setShowPassword(!showPasswordState);

    const [showPasswordConfirmState, setShowPasswordConfirm] = useState(false);
    const showPasswordConfirm = () => setShowPasswordConfirm(!showPasswordConfirmState);

    const user = useUserStore((state) => state.user);

    const [isOpen, setIsOpen] = useState(false);

    const handleOpen = () => setIsOpen(true);
    const handleClose = () => setIsOpen(false);

    return (
        <>
            <Stack direction="row" spacing={4} float={"right"} pt={"20px"} pr={"20px"}>
                {!user ? (
                    <Button colorScheme='teal' variant='solid' onClick={handleOpen}>
                        Регистрация
                    </Button>
                ) : (
                    <Button colorScheme='teal' variant='solid'>
                        Кабинет
                    </Button>
                )}
            </Stack>

            <Modal isOpen={isOpen} onClose={handleClose}>
                <ModalOverlay />
                <ModalContent>
                    <ModalHeader>Регистрация</ModalHeader>
                    <ModalCloseButton />
                    <ModalBody>
                        <Stack spacing={3}>
                            <Input variant='outline' placeholder='Имя' name="name" />
                            <InputGroup>
                                <InputLeftElement pointerEvents='none' color='gray.300' fontSize='1.2em'>
                                @
                                </InputLeftElement>
                                <Input placeholder='Введите почту' name="email" type="email" />
                            </InputGroup>
                            <InputGroup size='md'>
                                <Input
                                    pr='4.5rem'
                                    type={showPasswordState ? 'text' : 'password'}
                                    placeholder='Пароль'
                                    name="password"
                                />
                                <InputRightElement width='4.5rem'>
                                    <Button h='1.75rem' size='sm' onClick={showPassword}>
                                    {showPasswordState ? 'Hide' : 'Show'}
                                    </Button>
                                </InputRightElement>
                            </InputGroup>
                            <InputGroup size='md'>
                                <Input
                                    pr='4.5rem'
                                    type={showPasswordConfirmState ? 'text' : 'password'}
                                    placeholder='Повторите пароль'
                                    name="password-confirmated"
                                />
                                <InputRightElement width='4.5rem'>
                                    <Button h='1.75rem' size='sm' onClick={showPasswordConfirm}>
                                    {showPasswordConfirmState ? 'Hide' : 'Show'}
                                    </Button>
                                </InputRightElement>
                            </InputGroup>
                        </Stack>
                    </ModalBody>
                    <ModalFooter>
                        <Button colorScheme='blue' mr={3}>
                            Регистрация
                        </Button>
                        <Button onClick={handleClose}>Закрыть</Button>
                    </ModalFooter>
                </ModalContent>
            </Modal>
        </>
    );
};

export default MenuHeader;