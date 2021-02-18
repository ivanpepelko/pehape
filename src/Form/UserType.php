<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\Choice;

class UserType extends AbstractType
{
    /**
     * @var array|string[]
     */
    private array $controlLevelChoices = [
        'Admin'    => 'ROLE_ADMIN',
        'Korisnik' => 'ROLE_USER',
    ];

    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        if ($authorizationChecker->isGranted('ROLE_SUPERADMIN')) {
            $this->controlLevelChoices = array_merge(['Superadmin' => 'ROLE_SUPERADMIN'], $this->controlLevelChoices);
        }
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (PostSubmitEvent $event) {
                $role = $event->getForm()->get('control_level')->getData();

                /** @var User $user */
                $user = $event->getData();
                $previousRoles = $user->getRoles();

                foreach ($previousRoles as $previousRole) {
                    if (!$this->authorizationChecker->isGranted($previousRole)) {
                        return;
                    }
                }

                $user->setRoles([$role]);
            }
        );

        $builder
            ->add('username', null, ['disabled' => true])
            ->add(
                'control_level',
                ChoiceType::class,
                [
                    'mapped'      => false,
                    'label'       => 'Razina pristupa',
                    'choices'     => $this->controlLevelChoices,
                    'constraints' => new Choice(array_values($this->controlLevelChoices)),
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}
