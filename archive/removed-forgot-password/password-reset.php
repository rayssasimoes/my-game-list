<?php
// Backup copy of the removed includes/password-reset.php
// Created during cleanup on 2025-11-25
// Original functionality removed from active site.
http_response_code(410);
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Funcionalidade de redefinição de senha (backup).']);
exit;
