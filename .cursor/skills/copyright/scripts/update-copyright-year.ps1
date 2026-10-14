# Update copyright year in files changed since the start of this year.
#
# Usage:
#   .\update-copyright-year.ps1                       # Dry run (default)
#   .\update-copyright-year.ps1 -Apply                # Apply changes
#   .\update-copyright-year.ps1 -Since 2025           # Custom year threshold
#   .\update-copyright-year.ps1 -SinceTag v5.1.0      # Files changed since tag
#
# Finds every tracked file with a git commit since Jan 1 of -Since
# (or since a given tag). On @copyright and Copyright (C) headers only:
# a lone year lower than the current year becomes "YEAR - <current>",
# and a range end year lower than the current year is set to the current year.

param(
	[switch] $Apply,
	[int]    $Since    = (Get-Date).Year,
	[string] $SinceTag
)

function Replace-CopyrightMatch {
	param(
		[string]     $Text,
		[regex]      $Pattern,
		[scriptblock] $Replacement
	)

	$found = $Pattern.Matches($Text)
	if ($found.Count -eq 0) { return $Text }

	$sb   = New-Object System.Text.StringBuilder
	$last = 0

	foreach ($m in $found) {
		[void] $sb.Append($Text.Substring($last, $m.Index - $last))
		[void] $sb.Append((& $Replacement $m))
		$last = $m.Index + $m.Length
	}

	[void] $sb.Append($Text.Substring($last))
	return $sb.ToString()
}

function Update-CopyrightHeader {
	param(
		[string] $Text,
		[int]    $Year,
		[regex]  $RangePattern,
		[regex]  $SinglePattern
	)

	$Text = Replace-CopyrightMatch $Text $RangePattern {
		param($m)
		$end = [int] $m.Groups[2].Value
		if ($end -ge $Year) { return $m.Value }
		return $m.Groups[1].Value + $Year
	}

	return Replace-CopyrightMatch $Text $SinglePattern {
		param($m)
		$start = [int] $m.Groups[2].Value
		if ($start -ge $Year) { return $m.Value }
		return $m.Groups[1].Value + $m.Groups[2].Value + " - $Year"
	}
}

$root = git rev-parse --show-toplevel 2>$null
if (-not $root) {
	Write-Error 'Not inside a Git repository.'
	exit 1
}

Push-Location $root

try {
	$currentYear = (Get-Date).Year

	if ($SinceTag) {
		$tagRef = git rev-parse --verify $SinceTag 2>$null
		if (-not $tagRef) {
			Write-Error "Tag '$SinceTag' not found."
			exit 1
		}
		$label = "tag $SinceTag"
		$files = git diff --diff-filter=ACMR --name-only "$SinceTag...HEAD"
	} else {
		$sinceDate = "$Since-01-01"
		$label     = $sinceDate
		$files = git log --since=$sinceDate --diff-filter=ACMR --name-only --pretty=format:
	}

	$files = @(
		$files |
			Where-Object { $_ -ne '' } |
			Sort-Object -Unique |
			Where-Object { Test-Path -LiteralPath $_ } |
			Where-Object { $_ -notmatch '\.min\.(js|css)$' }
	)

	if (-not $files) {
		Write-Host "No files changed since $label."
		exit 0
	}

	$rangePattern  = [regex] '(?i)((?:@copyright|Copyright\s+\(c\))\s+\d{4}\s*-\s+)(\d{4})\b'
	$singlePattern = [regex] '(?i)((?:@copyright|Copyright\s+\(c\))\s+)(\d{4})(?!\s*-)\b'
	$utf8          = New-Object System.Text.UTF8Encoding $false
	$updated       = New-Object System.Collections.Generic.List[string]

	foreach ($file in $files) {
		$bytes = [System.IO.File]::ReadAllBytes((Resolve-Path -LiteralPath $file))
		if ([Array]::IndexOf($bytes, [byte] 0) -ge 0) { continue }

		$hasBom = $bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF
		$text   = $utf8.GetString($bytes)
		if ($text.Length -gt 0 -and [int][char]$text[0] -eq 0xFEFF) {
			$text = $text.Substring(1)
		}

		$next = Update-CopyrightHeader $text $currentYear $rangePattern $singlePattern
		if ($next -ceq $text) { continue }

		if ($Apply) {
			$outBytes = $utf8.GetBytes($next)
			if ($hasBom) {
				$combined = New-Object byte[] (3 + $outBytes.Length)
				$combined[0] = 0xEF
				$combined[1] = 0xBB
				$combined[2] = 0xBF
				[Buffer]::BlockCopy($outBytes, 0, $combined, 3, $outBytes.Length)
				$outBytes = $combined
			}
			[System.IO.File]::WriteAllBytes((Resolve-Path -LiteralPath $file), $outBytes)
		}
		$updated.Add($file)
	}
} finally {
	Pop-Location
}

if ($updated.Count -eq 0) {
	Write-Host 'No files needed updating.'
} else {
	$verb = if ($Apply) { 'Updated' } else { 'Would update' }
	Write-Host "$verb $($updated.Count) file(s):"
	$updated | ForEach-Object { Write-Host "  $_" }
}

if (-not $Apply -and $updated.Count -gt 0) {
	Write-Host ''
	Write-Host 'Run with -Apply to write changes.'
}
