<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة ضبط كلمة المرور</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            direction: rtl;
            text-align: right;
        }
        .container {
            max-width: 550px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e8ed;
        }
        .header {
            background: linear-gradient(135deg, #0ab3ba 0%, #0891b2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 30px;
            color: #334155;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1e293b;
        }
        .text {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 25px;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #0ab3ba;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(10, 179, 186, 0.25);
            transition: background-color 0.2s ease;
        }
        .code-container {
            text-align: center;
            margin: 25px 0;
        }
        .code-box {
            display: inline-block;
            background-color: #f1f5f9;
            border: 2px dashed #0ab3ba;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 6px;
            color: #0891b2;
            direction: ltr;
        }
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 25px 0;
        }
        .note {
            font-size: 13px;
            color: #94a3b8;
            text-align: center;
            margin-top: 15px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- الهيدر -->
        <div class="header">
            <h1>إعادة ضبط كلمة المرور 🔐</h1>
        </div>

        <!-- محتوى الإيميل -->
        <div class="content">
            <div class="greeting">أهلاً بك، {{ $name }} 👋</div>
            
            <p class="text">
                لقد تلقينا طلباً لإعادة ضبط كلمة المرور الخاصة بحسابك. هذا هو الرمز استخدمه لتحديث كلمة مرورك:
            </p>


            <div class="divider"></div>

            <div class="code-container">
                <div class="code-box">
                    {{ $code }}
                </div>
            </div>

            <p class="text" style="text-align: center; font-size: 13px;">
                هذا الرمز صالِح لمدة 10 دقيقة فقط.
            </p>

            <div class="note">
                إذا لم تطلب إعادة ضبط كلمة المرور، يمكنك إهمال هذا البريد بأمان ولن يتم تغيير أي شيء في حسابك.
            </div>
        </div>

        <!-- الفوتر -->
        <div class="footer">
            جميع الحقوق محفوظة &copy; {{ date('Y') }} منصتنا.
        </div>
    </div>

</body>
</html>