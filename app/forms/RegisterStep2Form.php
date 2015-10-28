<?php

namespace App\FrontModule\Forms;

use Nette\Application\UI\Form;

class RegisterStep2Form extends Form
{
    public function __construct($content)
    {
        parent::__construct();
        $this->addContainer('register_step_2');
        $this->addText('email', 'E-mail')
            ->setRequired()
            ->addRule(Form::EMAIL)
            ->setAttribute('placeholder', 'E-mail');
        $this->addPassword('password', 'Password')
            ->setRequired()
            ->setAttribute('placeholder', 'Password');
        $this->addRadioList('gender', 'Gender', ['male', 'female', 'alien'])
	        ->setRequired();
	    $this->addText('fbLink', 'Facebook profile link')
		    ->setRequired()
		    ->setAttribute('placeholder', 'Facebook profile link');
	    $this->addProtection();
        $this->addSubmit('submit', 'Finish register');
    }
}