<?php
/**
 * Abstract base class implementing {{#get_file_data:}} and mw.ext.externalData.getFileData.
 *
 * @author Yaron Koren
 * @author Alexander Mashin
 *
 */
abstract class EDConnectorPath extends EDConnectorBase {
	use EDConnectorParsable; // needs parser.

	/** @var string Real filepath. */
	protected $realPath;

	/**
	 * Constructor. Analyse parameters and wiki settings; set $this->errors.
	 *
	 * @param array &$args Arguments to parser or Lua function; processed by this constructor.
	 */
	protected function __construct( array &$args ) {
		// Parser.
		$this->prepareParser( $args );
		$this->error( $this->parseErrors );

		parent::__construct( $args );
	}

	/**
	 * Get data from absolute filepath. Set $this->values.
	 *
	 * @param string $path Real path to the file.
	 * @param string $alias An alias for real file path to show in error messages.
	 *
	 * @return array|null An array of values on success, null if error were encountered.
	 *
	 */
	protected function getDataFromPath( $path, $alias ) {
		if ( !file_exists( $path ) ) {
			$this->error( 'externaldata-missing-file', $alias );
			return null;
		}
		$file_contents = file_get_contents( $path );
		if ( empty( $file_contents ) ) {
			// Show an error message if there's nothing there.
			$this->error( 'externaldata-empty-file', $alias );
			return null;
		}
		$file_contents = EDEncodingConverter::toUTF8( $file_contents, $this->encoding );
		$values = $this->parse( $file_contents, [
			'__file' => [ $alias ],
			'__time' => [ time() ]
		] );
		$this->error( $this->parseErrors );
		return $values;
	}
}