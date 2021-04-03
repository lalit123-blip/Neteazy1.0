<?php

namespace Leadin\wp;

/**
 * Static class containing wrapper functions to access the file system.
 */
class FileSystem {
	/**
	 * Transform the a path relative to the plugin directory into an absolute path.
	 *
	 * @param String $file_path Relative path starting from the leadin folder.
	 * @return String Absolute path to the given file.
	 */
	private static function get_absolute_path( $file_path ) {
		return plugin_dir_path( LEADIN_BASE_PATH ) . $file_path;
	}

	/**
	 * Get content of local file
	 *
	 * @param String $file_path Relative path starting from the leadin folder.
	 * @return String Content of the file. Empty string if the file doesn't exist.
	 */
	public static function get_content( $file_path ) {
		if ( self::file_exists( $file_path ) ) {
			return file_get_contents( self::get_absolute_path( $file_path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}
		return '';
	}

	/**
	 * Return if the given file exists.
	 *
	 * @param String $file_path Relative path starting from the leadin folder.
	 * @return Boolean true if the given file exists.
	 */
	public static function file_exists( $file_path ) {
		return file_exists( self::get_absolute_path( $file_path ) );
	}
}
