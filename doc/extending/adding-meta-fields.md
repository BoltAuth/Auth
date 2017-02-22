Extending: Adding Meta Fields
-----------------------------

## Index

  * [Extension Loader Class](#extension-loader-class)
  * [Form Type Class](#form-type-class)
  * [Form Entity Class](#form-entity-class)


## Guide

### Extension Loader Class

Ensure you have the following import statements at the top ofyour class file.

```php
use Bolt\Extension\Bolt\Members\Event\FormBuilderEvent;
use Bolt\Extension\Bolt\Members\Event\MembersProfileEvent;
use Bolt\Extension\Bolt\Members\Form\MembersForms;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
```

The extension loading class will need a pre-save and form build events.

```php
    /**
     * {@inheritdoc}
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(MembersEvents::MEMBER_PROFILE_PRE_SAVE, [$this, 'onProfileSave']);
        $dispatcher->addListener(FormBuilderEvent::BUILD, [$this, 'onRequest']);
    }

    /**
     * Tell Members what fields we want to persist.
     *
     * @param MembersProfileEvent $event
     */
    public function onProfileSave(MembersProfileEvent $event)
    {
        // Meta fields that we want to register
        $fields = [
            'postcode',
        ];
        $event->addMetaEntryNames($fields);
    }

    /**
     * @param FormBuilderEvent $event
     */
    public function onRequest(FormBuilderEvent $event)
    {
        if ($event->getName() !== MembersForms::PROFILE_EDIT && $event->getName() !== MembersForms::PROFILE_VIEW) {
            return;
        }
        $app = $this->getContainer();

        // This is your custom Type class that extends \Bolt\Extension\Bolt\Members\Form\Type\ProfileEditType
        $type = new \Bolt\Extension\AuthorName\ExtensionName\Form\Type\ProfileEditType($app['members.config']);

        // This is the class name of your custom eneity
        $entityClassName = \Bolt\Extension\AuthorName\ExtensionName\Form\Entity\Profile::class;

        $event->setType($type);
        $event->setEntityClass($entityClassName);
    }
```

### Form Type Class

Create the file `src/Form/Type/ProfileEditType.php` adding the desired
`postcode` field.


```php
use Bolt\Extension\Bolt\Members\Form\Type\ProfileEditType as MembersProfileEditType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Bolt\Translation\Translator as Trans;

class ProfileEditType extends MembersProfileEditType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('postcode', Type\TextType::class, [
                'label_attr'  => [
                    'class' => 'main col-xs-12'
                ],
                'attr'        => [
                    'class' => 'form-control large'
                ],
                'label'       => Trans::__('Postcode:'),
                'constraints' => [
                ],
                'required'    => false,
            ])
        ;
    }
}
```


### Form Entity Class

Create the file `src/Form/Entity/Profile.php` adding the desired `postcode`
field.

```php
use Bolt\Extension\Bolt\Members\Form\Entity\Profile as BaseProfile;
use Symfony\Component\Validator\Constraints as Assert;

class Profile extends BaseProfile
{
    /** @var string */
    protected $postcode;

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
    }
}

```
