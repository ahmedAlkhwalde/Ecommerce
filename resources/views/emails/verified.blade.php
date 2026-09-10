<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كود التحقق</title>
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
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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
        .code-container {
            text-align: center;
            margin: 30px 0;
        }
        .code-box {
            display: inline-block;
            background-color: #f1f5f9;
            border: 2px dashed #6366f1;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #4f46e5;
        }
        .note {
            font-size: 13px;
            color: #94a3b8;
            text-align: center;
            margin-top: 20px;
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
            <h1>تأكيد البريد الإلكتروني</h1>
        </div>

        <!-- محتوى الإيميل -->
        <div class="content">
            <div class="greeting">أهلاً بك، {{ $name }} 👋</div>
            
            <p class="text">
                شكراً لتسجيلك معنا! لإكمال عملية التحقق وتفعيل حسابك، يرجى استخدام رمز التحقق المكون من 6 أرقام التالي:
            </p>

            <!-- صندوق الكود -->
            <div class="code-container">
                <div class="code-box">
                    {{ $code }}
                </div>
            </div>

            <p class="text" style="text-align: center;">
                هذا الرمز صالِح لمدة 10 دقائق فقط. لا تشارك هذا الرمز مع أي شخص.
            </p>

            <div class="note">
                إذا لم تقم بإنشاء حساب، يمكنك إهمال هذه الرسالة بآمان.
            </div>
        </div>

        <!-- الفوتر -->
        <div class="footer">
            جميع الحقوق محفوظة &copy; {{ date('Y') }} متجرنا الإلكتروني.
        </div>
    </div>

</body>
</html>