param(
	[Parameter(ValueFromRemainingArguments = $true)]
	[string[]] $Paths,
	[switch] $Recurse
)

$utf8NoBom = New-Object System.Text.UTF8Encoding $false
$script:useful = 0
$script:useless = 0
$textExtensions = @(
	'.md',
	'.mdc',
	'.txt',
	'.php',
	'.js',
	'.css',
	'.json',
	'.xml',
	'.pot',
	'.svg'
)

function Test-TextExtension {
	param([string] $FilePath)

	return $textExtensions -contains [System.IO.Path]::GetExtension($FilePath).ToLower()
}

function Normalize-LfFile {
	param([string] $FilePath)

	if (-not (Test-Path -LiteralPath $FilePath -PathType Leaf)) {
		Write-Warning "Skip missing file: $FilePath"
		return
	}

	if (-not (Test-TextExtension -FilePath $FilePath)) {
		Write-Warning "Skip non-text extension: $FilePath"
		return
	}

	$bytes = [System.IO.File]::ReadAllBytes($FilePath)

	if ($bytes.Length -eq 0 -or $bytes -notcontains 13) {
		Write-Output "USELESS already LF: $FilePath"
		$script:useless++
		return
	}

	$text = [System.Text.Encoding]::UTF8.GetString($bytes)

	if ($text.Length -gt 0 -and [int][char]$text[0] -eq 0xFEFF) {
		$text = $text.Substring(1)
	}

	$normalized = ($text -replace "`r`n", "`n") -replace "`r", "`n"

	if (-not $normalized.EndsWith("`n")) {
		$normalized += "`n"
	}

	[System.IO.File]::WriteAllText($FilePath, $normalized, $utf8NoBom)
	Write-Output "USEFUL converted: $FilePath"
	$script:useful++
}

function Get-TextFilesFromDirectory {
	param(
		[string] $DirectoryPath,
		[bool] $RecurseFiles
	)

	$params = @{
		LiteralPath = $DirectoryPath
		File        = $true
	}

	if ($RecurseFiles) {
		$params.Recurse = $true
	}

	return Get-ChildItem @params | Where-Object {
		$textExtensions -contains $_.Extension.ToLower()
	}
}

$files = @()

foreach ($path in $Paths) {
	if (-not $path) {
		continue
	}

	$resolved = Resolve-Path -LiteralPath $path -ErrorAction SilentlyContinue

	if (-not $resolved) {
		Write-Warning "Skip missing path: $path"
		continue
	}

	foreach ($item in $resolved) {
		if (Test-Path -LiteralPath $item -PathType Container) {
			$files += Get-TextFilesFromDirectory -DirectoryPath $item -RecurseFiles $Recurse
		}
		elseif (Test-TextExtension -FilePath $item.Path) {
			$files += Get-Item -LiteralPath $item.Path
		}
		else {
			Write-Warning "Skip non-text extension: $($item.Path)"
		}
	}
}

$files | Select-Object -ExpandProperty FullName -Unique | ForEach-Object {
	Normalize-LfFile -FilePath $_
}

Write-Output "RESULT: useful=$script:useful useless=$script:useless"
