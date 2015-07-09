<?php

namespace App\Presenters;

use FLaRM\Framework;
use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderIndex()
	{
		$this->template->nette_version = Nette\Framework::VERSION;
		$this->template->flarm_version = Framework::VERSION;
	}

}
