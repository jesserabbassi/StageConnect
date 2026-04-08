<?php
/**
 * StageConnect — Logout Handler
 * Destroys session and redirects to login
 */

require_once __DIR__ . '/php/functions.php';

// Destroy the session securely
$_SESSION = [];                          // Clear all session variables
session_destroy();                       // Destroy the session

// Set a flash message for the login page
// (We need to start a new session to set the flash)
session_start();
setFlash('success', 'Vous avez été déconnecté avec succès.');

redirect('login.php');
