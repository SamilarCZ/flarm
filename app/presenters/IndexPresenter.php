<?php

namespace App\Presenters;

use Nette,
	App\Model;
use FLaRM\Framework;


/**
 * Index presenter.
 */
class IndexPresenter extends BasePresenter
{

	public function renderIndex()
	{
		$this->template->nette_version = Nette\Framework::VERSION;
		$this->template->flarm_version = Framework::VERSION;
	}

}
