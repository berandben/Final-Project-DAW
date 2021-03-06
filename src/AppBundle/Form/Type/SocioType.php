<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Socio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class SocioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();
                $form
                    ->add('nif', null, [
                        'label' => 'Nif:',
                        'property_path' => 'usuario.nif',
                        'required' => true,
                        'constraints' => [
                            new Assert\Regex('/^[0-9]{8}[A-Z a-z]{1}$/')
                        ]
                    ])
                    ->add('nombre', null, [
                        'label' => 'Nombre:',
                        'property_path' => 'usuario.nombre',
                        'required' => true,
                        'constraints' => [
                            new Assert\Regex('/^[A-Z a-zÑñáéíóúÁÉÍÓÚ , .]*$/')
                        ]
                    ])
                    ->add('apellidos', null, [
                        'label' => 'Apellidos:',
                        'property_path' => 'usuario.apellidos',
                        'required' => true,
                        'constraints' => [
                            new Assert\Regex('/^[A-Z a-zÑñáéíóúÁÉÍÓÚ , .]*$/')
                        ]
                    ])
                    ->add('direccion', null, [
                        'label' => 'Dirección:',
                        'required' => true,
                        'property_path' => 'usuario.direccion'
                    ])
                    ->add('codigoPostal', null, [
                        'label' => 'Código postal:',
                        'property_path' => 'usuario.codigoPostal',
                        'required' => true,
                        'constraints' => [
                            new Assert\Regex('/^[0-9]{5}$/')
                        ]
                    ])
                    ->add('localidad', null, [
                        'label' => 'Localidad:',
                        'property_path' => 'usuario.localidad',
                        'constraints' => [
                            new Assert\Regex('/^[A-Z a-zÑñáéíóúÁÉÍÓÚ]*$/')
                        ]
                    ])
                    ->add('provincia', ChoiceType::class, [
                        'label' => 'Provincia:',
                        'property_path' => 'usuario.provincia',
                        'placeholder' => '[Ninguna]',
                        'choices' => $options['provincias']
                    ])
                    ->add('telefono', null , [
                        'label' => 'Telefono: (opcional)',
                        'property_path' => 'usuario.telefono',
                        'required' => false,
                        'constraints' => [
                            new Assert\Regex('/^[0-9]{9}$/')
                        ]
                    ])
                    ->add('email', EmailType::class, [
                        'label' => 'Correo electrónico: (opcional)',
                        'property_path' => 'usuario.email',
                        'required' => false
                    ])
                    ->add('descuento', PercentType::class, [
                        'label' => 'Descuento:',
                        'property_path' => 'usuario.descuento'
                    ]);
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Socio::class,
            'translation_domain' => false,
            'provincias' => null
        ]);
    }
}
