<?php

namespace App\FrontModule\Forms;

use Nette\Application\UI\Form;

class RegisterForm extends Form
{
    public function __construct($content)
    {
        parent::__construct();
        $this->addContainer('sign_in');
        $this->addText('email', 'E-mail')
            ->setRequired()
            ->addRule(Form::EMAIL)
            ->setAttribute('placeholder', 'E-mail');
//        $this->addPassword('password', 'Password')
//            ->setRequired()
//            ->setAttribute('placeholder', 'Password');
        $this->addProtection();
        $this->addSubmit('submit', 'Register');
    }
}