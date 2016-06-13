<?php

namespace ChoferesBundle\Controller;

use ChoferesBundle\Entity\ComprobantePago;
use ChoferesBundle\Form\ComprobantePagoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ComprobantePagoController extends Controller
{
    // $id => curso id
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

    public function editAction($idCurso, $idComprobantePago)
    {
        $em = $this->getDoctrine()->getManager();
        
        $curso = $em->getRepository('ChoferesBundle:Curso')->find($idCurso);
        if (! $curso) {
            throw $this->createNotFoundException('Unable to find Curso entity.');
        }

        $comprobantePago = $em->getRepository('ChoferesBundle:ComprobantePago')->find($idComprobantePago);
        if (! $comprobantePago) {
            throw $this->createNotFoundException('Unable to find ComprobantePago entity.');
        }

        $editForm = $this->createForm(new ComprobantePagoType(), $comprobantePago);

        return $this->render('ChoferesBundle:ComprobantePago:edit.html.twig', [
            'curso'           => $curso,
            'comprobantePago' => $comprobantePago,
            'edit_form'       => $editForm->createView(),
        ]);
    }

    public function updateAction(Request $request, $idCurso, $idComprobantePago)
    {
        $em = $this->getDoctrine()->getManager();
        
        $curso = $em->getRepository('ChoferesBundle:Curso')->find($idCurso);
        if (! $curso) {
            throw $this->createNotFoundException('Unable to find Curso entity.');
        }

        $comprobantePago = $em->getRepository('ChoferesBundle:ComprobantePago')->find($idComprobantePago);
        if (! $comprobantePago) {
            throw $this->createNotFoundException('Unable to find ComprobantePago entity.');
        }

        $editForm = $this->createForm(new ComprobantePagoType(), $comprobantePago);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($comprobantePago);
            $em->flush();    
            
            $this->get('session')->getFlashBag()->add('success', 'Comprobante de Pago actualizado.');

            

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

        // if ($editForm->isValid()) {
        //     $em->persist($entity);
        //     $em->flush();
        //     $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

        //     return $this->redirect($this->generateUrl('docente_edit', array('id' => $id)));
        // } else {
        //     $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        // }

        return $this->render('ChoferesBundle:ComprobantePago:edit.html.twig', [
            'curso'           => $curso,
            'comprobantePago' => $comprobantePago,
            'edit_form'       => $editForm->createView(),
        ]);
    }

    public function deleteAction(Request $request, $idCurso, $idComprobantePago)
    {
        $em = $this->getDoctrine()->getManager();
        $comprobantePago = $em->getRepository('ChoferesBundle:ComprobantePago')->find($idComprobantePago);

        if (! $comprobantePago) {
            throw $this->createNotFoundException('Unable to find ComprobantePago entity.');
        }

        $em->remove($comprobantePago);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Comprobante eliminado satisfactoriamente.');

        return $this->redirect($this->generateUrl('curso_show', ['id' => $idCurso]));
    }
}
