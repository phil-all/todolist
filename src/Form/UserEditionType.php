<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * UserEditionType class
 * @package App\Form
 */
class UserEditionType extends AbstractType
{
    /**
     * @see AbstractType
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label'           => 'Nom d\'utilisateur',
                'constraints'     => [
                    new NotBlank([
                        'message' => 'Vous devez saisir un nom d\'utilisateur.'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label'       => 'Adresse email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez saisir une adresse email.'
                    ]),
                    new Email([
                        'message' => 'Le format de l\'adresse n\'est pas correcte.'
                    ])
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur'    => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'expanded' => true,
            ])
            ->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                fn ($rolesAsArray) => count($rolesAsArray) ? $rolesAsArray[0] : null,
                fn ($rolesAsString) => [$rolesAsString]
            ));
    }
}
