<?php

namespace MediaWiki\Extension\CollisionManager\Specials;

use MediaWiki\SpecialPage\IncludableSpecialPage;
use MediaWiki\Extension\CollisionManager\DisambigBuilder;
use MediaWiki\Title\Title;
use MediaWiki\Html\FormOptions;
use MediaWiki\Xml\Xml;
use MediaWiki\Html\Html;
class SpecialDisambig extends IncludableSpecialPage
{
	/** @var DisambigBuilder */
	private $disambigBuilder;

	/** @var FormOptions */
	protected $opts;

	/** @var Title */
	protected $target;

	/**
	 * @param DisambigBuilder $disambigBuilder
	 */
	public function __construct(
		DisambigBuilder $disambigBuilder
	) {
		parent::__construct('Disambig');
		$this->disambigBuilder = $disambigBuilder;
	}

	/**
	 * @param ?string $query
	 */
	function execute($query)
	{
		$output = $this->getOutput();

		$this->setHeaders();
		$this->outputHeader();

		$opts = new FormOptions();

		$opts->add('target', '');

		$opts->fetchValuesFromRequest($this->getRequest());

		if ($query !== null) {
			$opts->setValue('target', $query);
		}

		$this->opts = $opts;

		$this->target = Title::newFromURL($opts->getValue('target'));
		if ($this->target === null) {
			$output->addHTML($this->disambigForm());
			return;
		}

		$text = $this->target->getPrefixedText();

		$output->setPageTitle( $this->msg( 'disambig-title', $text )->text() );
		$output->addBacklinkSubtitle($this->target);

		$output->addHTML($this->disambigBuilder->getDisambig($text, -1));
	}

	function disambigForm()
	{
		// We get nicer value from the title object
		$this->opts->consumeValue('target');

		$target = $this->target ? $this->target->getPrefixedText() : '';

		# Build up the form
		$f = Html::openElement( 'form', [ 'action' => wfScript() ] );

		# Values that should not be forgotten
		$f .= Html::hidden('title', $this->getPageTitle()->getPrefixedText());
		foreach ($this->opts->getUnconsumedValues() as $name => $value) {
			$f .= Html::hidden($name, $value);
		}

		$f .= Html::openElement( 'fieldset' ) . Html::element( 'legend', [], $this->msg( 'disambig' )->text() );

		# Target input
		$f .= Html::label( $this->msg( 'whatlinkshere-page' )->text(), 'mw-disambig-target' ) . ' ';
		$f .= Html::input( 'target', $target, 'text', [
			'id' => 'mw-disambig-target',
			'size' => 40,
		] );

		$f .= ' ';

		# Submit
		$f .= Html::submitButton( $this->msg( 'allpagessubmit' )->text() );

		# Close
		$f .= Html::closeElement('fieldset') . Html::closeElement( 'form' ) . "\n";

		return $f;
	}

	protected function getGroupName()
	{
		return 'pagetools';
	}
}
