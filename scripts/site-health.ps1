$ErrorActionPreference = 'Continue'
$results = @()

function Test-Url {
    param([string]$Url, [string[]]$MustContain = @())
    $tmp = Join-Path $env:TEMP ('sg_' + [guid]::NewGuid().ToString() + '.html')
    $code = & curl.exe -sL -o $tmp -w '%{http_code}' --max-time 30 $Url
    $body = if (Test-Path $tmp) { Get-Content -Raw $tmp } else { '' }
    $miss = @()
    foreach ($p in $MustContain) {
        if ($body -notmatch [regex]::Escape($p)) { $miss += $p }
    }
    $item = [pscustomobject]@{
        url = $Url
        status = [int]$code
        bytes = $body.Length
        miss = ($miss -join '|')
        ok = (([int]$code -ge 200 -and [int]$code -lt 400) -and $miss.Count -eq 0)
    }
    Remove-Item $tmp -ErrorAction SilentlyContinue
    return $item
}

$checks = @(
    @{ u = 'https://freddyk9.sg-host.com/en'; m = @('SINO GOOD','Hotel Supplies') },
    @{ u = 'https://freddyk9.sg-host.com/zh'; m = @('SINO GOOD','酒店用品','案例') },
    @{ u = 'https://freddyk9.sg-host.com/zh-hant'; m = @('SINO GOOD','案例') },
    @{ u = 'https://freddyk9.sg-host.com/en/products'; m = @('All Products','SINO GOOD') },
    @{ u = 'https://freddyk9.sg-host.com/zh/products'; m = @('全部产品','SINO GOOD') },
    @{ u = 'https://freddyk9.sg-host.com/en/cases'; m = @('Case Studies','SINO GOOD') },
    @{ u = 'https://freddyk9.sg-host.com/zh/cases'; m = @('项目案例','SINO GOOD') },
    @{ u = 'https://freddyk9.sg-host.com/zh-hant/cases'; m = @('項目案例','SINO GOOD') },
    @{ u = 'https://freddyk9.sg-host.com/en/about'; m = @('About SINO GOOD') },
    @{ u = 'https://freddyk9.sg-host.com/zh/about'; m = @('关于 SINO GOOD') },
    @{ u = 'https://freddyk9.sg-host.com/en/contact'; m = @('_token','Send Message') },
    @{ u = 'https://freddyk9.sg-host.com/zh/contact'; m = @('_token','发送留言') },
    @{ u = 'https://freddyk9.sg-host.com/admin/login'; m = @('email','password') }
)

foreach ($c in $checks) {
    $r = Test-Url $c.u $c.m
    $results += $r
    Write-Output ("{0} | {1} | miss=[{2}] | {3}" -f $r.status, $r.bytes, $r.miss, $r.url)
}

$cats = @('hotel-supplies','swimming-pool','wall-panels','jw-custom-profiles','bathroom-products','indoor-outdoor-furniture')
foreach ($cat in $cats) {
    $u = "https://freddyk9.sg-host.com/en/category/$cat"
    $r = Test-Url $u @()
    $results += $r
    Write-Output ("{0} | {1} | category | {2}" -f $r.status, $r.bytes, $u)
}

Write-Output '====ASSETS===='
$home = Join-Path $env:TEMP 'sg_home.html'
curl.exe -sL -o $home 'https://freddyk9.sg-host.com/en'
$homeHtml = Get-Content -Raw $home
$rx = [regex]'/build/assets/[A-Za-z0-9._-]+'
$assets = $rx.Matches($homeHtml) | ForEach-Object { $_.Value } | Select-Object -Unique
foreach ($a in $assets) {
    $u = "https://freddyk9.sg-host.com$a"
    $c = & curl.exe -sL -o NUL -w '%{http_code} %{size_download}' --max-time 20 $u
    Write-Output "$c $u"
}

Write-Output '====REDIRECT===='
curl.exe -sI --max-time 20 'https://freddyk9.sg-host.com/' | Select-String -Pattern 'HTTP/|Location:'

$out = Join-Path $PSScriptRoot 'site-health-results.json'
# When run via temp, write to workspace
$out = 'C:\Users\fredd\OneDrive\Desktop\CURSOR\sino_good_website\site-health-results.json'
$results | ConvertTo-Json -Depth 4 | Set-Content -Path $out -Encoding UTF8
Write-Output "WROTE $out"
