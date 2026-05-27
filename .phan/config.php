<?php
/**
 * Phan configuration for the AST package.
 *
 * @package tangible/ast
 */

require_once __DIR__ . '/../../../.phan/config.php';

return make_phan_config(
    __DIR__ . '/..',
    array(
        'directory_list' => array(
            'src',
        ),
        'exclude_analysis_directory_list' => array(),
        'baseline_path' => __DIR__ . '/baseline.php',
    )
);
