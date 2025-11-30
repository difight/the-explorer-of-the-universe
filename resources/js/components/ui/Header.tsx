import AnimatedSpaceHeader from '@/components/AnimatedSpaceHeader';
import MenuHeader from '@/components/ui/MenuHeader';
import { Container, Button, Box } from '@chakra-ui/react';
const Header = () => {
  return (
    <Container maxW="100%" p={0} position={"relative"}>
        <Container maxW="100%" p={0} float="left">
            <AnimatedSpaceHeader />
        </Container>
        <Container position={"absolute"} maxW={"100%"}>
            <MenuHeader />
        </Container>
    </Container>
  );
};

export default Header;
