# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

Write-Host "[smoke] Product M0 canon files check"
if (-Not (Test-Path "./canon/schema/product.schema.json")) { throw "Missing product.schema.json" }
if (-Not (Test-Path "./canon/contract/product.openapi.json")) { throw "Missing product.openapi.json" }
Write-Host "[smoke] OK"
