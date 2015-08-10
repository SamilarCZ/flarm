<?php
	namespace App\service\ResourceService;

	use Latte\Compiler;
	use Latte\MacroNode;
	use Latte\Macros\MacroSet;
	use Latte\PhpWriter;

	class ResourceMacro extends MacroSet {
		public static function install(Compiler $compiler) {
			$self = new self($compiler);
			$self->addMacro('resource', array(
				$self,
				'macroResource'
			));
		}

		public function macroResource(MacroNode $node, PhpWriter $writer) {
			return $writer->write(sprintf('echo $resourceService->link(%s);', $node->args));
		}
	}
