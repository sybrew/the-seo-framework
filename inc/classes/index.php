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
 *       |-> Final
 *
 * ## Failsafe:
 *    - Silencer
 *       |-> Final
 *
 * ## Façade (bottom is called first):
 *    -  | Core
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
 *       | Profile
 *       | Admin_Pages
 *       | Sanitize
 *       | Site_Options
 *       | Cache
 *       | Load
 *          |-> Final
 *          |-> Instanced in function `the_seo_framework()`
 */
