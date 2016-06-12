<?php

namespace ChoferesBundle\Controller;

use ChoferesBundle\Entity\ComprobantePago;
use ChoferesBundle\Form\ComprobantePagoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ComprobantePagoController extends Controller
{
    public function createAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $curso = $em->getRepository('ChoferesBundle:Curso')->find($id);

        if (! $curso) {
            throw $this->createNotFoundException('Unable to find Curso entity.');
        }

        $comprobantePago = new ComprobantePago();
        $comprobantePagoForm = $this->createForm(new ComprobantePagoType(), $comprobantePago);
        
        $comprobantePagoForm->bind($request);

        if ($comprobantePagoForm->isValid()) {
            $comprobantePago->setCurso($curso);
            
            $em->persist($comprobantePago);
            $em->flush();    
            
            $this->get('session')->getFlashBag()->add('success', 'Comprobante de pago cargado.');
        } else {
            foreach ($comprobantePagoForm->getErrors() as $error) {
                $this->get('session')->getFlashBag()->add(
                        'error',
                        'Comprobante Pago :: ' . $error->getMessage()
                    );
            }

            $errors = $this->get('validator')->validate($comprobantePago);
            foreach($errors as $error) {
                $this->get('session')->getFlashBag()->add('error', 'Comprobante Pago ::: ' . $error->getMessage());
            }
        }

        return $this->redirect($this->generateUrl('curso_show', ['id' => $id]));
    }
}
