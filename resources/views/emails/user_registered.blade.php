<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun RJ TEKNIK Berhasil</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F7F5F0; margin: 0; padding: 24px; color: #18181B; -webkit-font-smoothing: antialiased;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #E8E4DE; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
        <!-- Kop Surat RJ TEKNIK -->
        <tr>
            <td style="background-color: #8C5E3C; padding: 28px 32px; text-align: left;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: 0.5px;">
                                RJ TEKNIK
                            </h1>
                            <p style="color: #F3EFEA; margin: 4px 0 0 0; font-size: 11px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase;">
                                COFFEE ROASTER ENGINEERING & FABRICATION
                            </p>
                        </td>
                        <td align="right">
                            <span style="background-color: rgba(255,255,255,0.18); color: #ffffff; font-size: 11px; font-weight: bold; padding: 6px 12px; border-radius: 20px; display: inline-block;">
                                Akun Terdaftar
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Isi Surat -->
        <tr>
            <td style="padding: 32px 32px 24px 32px; line-height: 1.6; font-size: 14px; color: #27272A;">
                <h2 style="font-size: 18px; font-weight: 800; color: #18181B; margin-top: 0; margin-bottom: 12px;">
                    Halo, <span style="color: #8C5E3C;">{{ $user->name }}</span>
                </h2>
                <p style="margin-top: 0; margin-bottom: 16px; color: #52525B;">
                    Selamat! Akun Anda telah berhasil terdaftar pada platform layanan web resmi <strong>RJ TEKNIK Coffee Roaster Engineering</strong>.
                </p>
                <p style="margin-bottom: 20px; color: #52525B;">
                    Sekarang Anda dapat berkonsultasi spesifikasi teknis mesin roasting kopi dengan asisten AI kami maupun teknisi workshop, melihat rincian katalog, memantau antrean fabrikasi mesin, dan melakukan pemesanan Pre-Order mesin secara terstruktur.
                </p>

                <!-- Kotak Detail Akun Terdaftar -->
                <div style="background-color: #FAF9F5; border: 1px solid #E8E4DE; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <div style="font-size: 11px; font-weight: 800; color: #8C5E3C; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                        Detail Akun Pengguna:
                    </div>
                    <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 13px;">
                        <tr>
                            <td width="35%" style="color: #71717A; font-weight: 500;">Nama Lengkap:</td>
                            <td style="font-weight: 700; color: #18181B;">{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td style="color: #71717A; font-weight: 500;">Email Terdaftar:</td>
                            <td style="font-weight: 700; color: #18181B;">{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td style="color: #71717A; font-weight: 500;">Metode Masuk:</td>
                            <td style="font-weight: 600; color: #8C5E3C;">{{ $loginMethod ?? 'Google Account' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #71717A; font-weight: 500;">Waktu Pendaftaran:</td>
                            <td style="color: #18181B;">{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
                        </tr>
                    </table>
                </div>

                <!-- Informasi Layanan & Pemesanan -->
                <div style="border-left: 3px solid #8C5E3C; padding-left: 14px; margin-bottom: 24px; font-size: 13px; color: #57534E;">
                    <p style="margin: 0;">
                        Seluruh pemesanan mesin roasting kopi RJ TEKNIK berskema <strong>Pre-Order (Perakitan Custom)</strong> dengan pembayaran <strong>Uang Muka (DP 20%)</strong> aman via Midtrans Snap, serta <strong>Pelunasan 80%</strong> saat mesin selesai dirakit dan lolos tahap quality control di bengkel workshop kami.
                    </p>
                </div>

                <p style="margin-top: 24px; margin-bottom: 4px; color: #18181B; font-size: 14px;">
                    Terima kasih telah mempercayakan kebutuhan mesin sangrai kopi Anda kepada RJ TEKNIK.
                </p>
                <p style="margin-top: 0; margin-bottom: 0; color: #71717A; font-size: 13px;">
                    <strong>Tim Workshop & Manajemen RJ TEKNIK</strong><br>
                    Jl. Dawar Blandong, Mojokerto / Gresik, Jawa Timur
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #F7F5F0; border-top: 1px solid #E8E4DE; padding: 20px 32px; text-align: center; font-size: 11px; color: #A1A1AA; line-height: 1.5;">
                Email ini dikirimkan secara otomatis oleh sistem web resmi RJ TEKNIK Coffee Roaster.<br>
                &copy; {{ date('Y') }} RJ TEKNIK Coffee Roaster Engineering. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
