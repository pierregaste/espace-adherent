<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AutocompleteInputType extends AbstractType
{
    public function getParent(): ?string
    {
        return TextType::class;
    }
}
