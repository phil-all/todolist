<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * TaskType class
 * @package App\Form
 */
class TaskType extends AbstractType
{
    /**
     * @see AbstractType
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label'       =>  'Titre',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez saisir un nom titre.'
                    ])
                ]
            ])
            ->add('content', TextareaType::class, [
                'label'       =>  'Description',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez saisir une description.'
                    ])
                ]
            ]);
    }
}
