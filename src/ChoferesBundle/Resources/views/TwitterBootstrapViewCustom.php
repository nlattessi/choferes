<?php


namespace ChoferesBundle\Resources\views;


use Pagerfanta\View\DefaultView;

class TwitterBootstrapViewCustom extends DefaultView
{
    protected function createDefaultTemplate()
    {
        return new TwitterBootstrapTemplateCustom();
    }

    protected function getDefaultProximity()
    {
        return 3;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twitter_bootstrap';
    }
}
