<?php
/**
 * Class call tree
 *
 * # Namespace: The_SEO_Framework
 *
 * ## Separated:
 *    - Deprecated
 *       |-> Final
 *    - Debug
 *       |-> Interface:
 *          -  Debug_Interface
 *       |-> Final
 *
 * ## Façade (bottom is called first):
 *    -  | Core
 *       | Compat
 *       | Query
 *       | Init
 *       | Admin_Init
 *       | Render
 *       | Detect
 *       | Post_Data
 *       | Term_Data
 *       | User_Data
 *       | Generate
 *       | Generate_Description
 *       | Generate_Title
 *       | Generate_Url
 *       | Generate_Image
 *       | Generate_Ldjson
 *       | Doing_It_Right
 *       | Profile
 *       | Inpost
 *       | Admin_Pages
 *       | Sanitize
 *       | Site_Options
 *       | Metaboxes
 *       | Sitemaps
 *       | Cache
 *       | Feed
 *       | Load
 *          |-> Interface:
 *             - Debug_Interface
 *          |-> Final
 *          |-> Instance
 *
 */
