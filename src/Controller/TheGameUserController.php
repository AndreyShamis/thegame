<?php

namespace App\Controller;

use App\Entity\TheGameUser;
use App\Form\TheGameUserType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class TheGameUserController
 * @package App\Controller
 * @Route("/user")
 */
class TheGameUserController extends Controller
{
    /**
     * @Route("/", name="the_game_user")
     */
    public function index()
    {
        return $this->render('the_game_user/index.html.twig', [
            'controller_name' => 'TheGameUserController',
        ]);
    }

    /**
     * @Route(path="/login", name="login")
     * @param Request $request
     * @param AuthenticationUtils $authUtils
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function login(Request $request, AuthenticationUtils $authUtils, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $error = $authUtils->getLastAuthenticationError();
        /** @var TheGameUser $user */
        $user = null;
        $ldapLogin = false;
        $use_ldap = getenv('USE_LDAP');
        $use_only_ldap = getenv('USE_ONLY_LDAP');
        $key = '_security.main.target_path'; #where "main" is your firewall name
        try {
            if ($request->isMethod('POST')) {
                $user_name = $request->request->get('_username');
                $password = $request->request->get('_password');
                // Retrieve the security encoder of symfony
                $user_manager = $this->getDoctrine()->getManager()->getRepository(TheGameUser::class);


                $user = $user_manager->loadUserByUsername($user_name);


                if ($user !== null) {
                    //$encoded_pass = $passwordEncoder->encodePassword($user, $password);
                    //$salt = $user->getSalt();
                    if (!$user->getIsActive()){
                        throw new AuthenticationException('The user is disabled.');
                    }
                    if (
                        ($passwordEncoder->isPasswordValid($user, $password) && !$ldapLogin && $user->getIsActive())
                        ||
                        ($ldapLogin && $user->isLdapUser() && $user->getIsActive())
                    ) {
                        // The password matches ! then proceed to set the user in session

                        //Handle getting or creating the user entity likely with a posted form
                        // The third parameter "main" can change according to the name of your firewall in security.yml
                        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                        $this->get('security.token_storage')->setToken($token);

                        // If the firewall name is not main, then the set value would be instead:
                        // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
                        $this->get('session')->set('_security_main', serialize($token));

                        // Fire the login event manually
                        $event = new InteractiveLoginEvent($request, $token);
                        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

                        if ($this->container->get('session')->has($key)) {
                            //set the url based on the link they were trying to access before being authenticated
                            $url = $this->container->get('session')->get($key);
                            return new RedirectResponse($url);
                        }

                        return $this->render('lbook/default/index.html.twig', array(
                        ));
                    }

                    throw new AuthenticationException('Username or Password not valid.');
                }

                throw new AuthenticationException('Username or Password not valid.');
            }

        } catch (AuthenticationException $ex) {
            // get the login error if there is one
            $error = $ex;
        }
        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();
        return $this->render('the_game_user/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register", name="user_registration")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \LogicException
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        // 1) build the form
        $user = new TheGameUser();
        $form = $this->createForm(TheGameUserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('home_index');
        }

        return $this->render(
            'the_game_user/register.html.twig',
            array('form' => $form->createView())
        );
    }
}
