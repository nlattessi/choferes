<?php

namespace ChoferesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\SecurityContextInterface;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('home'));
        }

        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = null;
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        return $this->render(
            'ChoferesBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );

    }

    public function redirectAction()
    {
        return $this->redirect($this->generateUrl('home'));
    }

    public function cambiarPasswordAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $password = $request->request->get('_password');
            $password2 = $request->request->get('_password2');

            if (empty($password) || empty($password2)) {
                $error = "Passwords no pueden ser vacias";
            } else if ($password === $password2) {
                $em = $this->getDoctrine()->getManager();
                $entity = $this->getUser();
                $encoder = $this->container
                   ->get('security.encoder_factory')
                   ->getEncoder($entity)
                ;
                $entity->setPassword($encoder->encodePassword($password, $entity->getSalt()));
                $em->persist($entity);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

                return $this->redirect($this->generateUrl('home'));
            } else {
                $error = "Passwords no coinciden";
            }
        } else {
            $error = NULL;
        }

        return $this->render('ChoferesBundle:Security:cambiar_password.html.twig', array('error' => $error));
    }
}
