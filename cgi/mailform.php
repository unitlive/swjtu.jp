<?php
/**
 * 西南交通大学日本校友会 - 邮件发送处理
 *
 * SMTP配置:
 * - 服务器: smtp.hetemail.jp
 * - 端口: 465 (SSL)
 * - 账户: web@swjtu.jp
 */

// PHPMailer 自动加载 (Composer)
// 如果使用Composer安装: composer require phpmailer/phpmailer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// SMTP 配置
define('SMTP_HOST', 'smtp.hetemail.jp');
define('SMTP_PORT', 465);
define('SMTP_USER', 'web@swjtu.jp');
define('SMTP_PASS', '<<管理者に連絡してください>>');
define('SMTP_FROM', 'web@swjtu.jp');
define('SMTP_FROM_NAME', '西南交通大学日本校友会');
define('MAIL_TO', 'info@swjtu.jp');

/**
 * 使用PHPMailer发送邮件
 */
function sendMailWithPHPMailer($to, $subject, $body, $replyTo, $replyToName) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP 服务器设置
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';
        
        // 发件人/收件人设置
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo($replyTo, $replyToName);
        
        // 邮件内容
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * 使用原生PHP socket发送SMTP邮件 (备用方案)
 */
function sendMailWithSocket($to, $subject, $body, $replyTo) {
    $smtpHost = 'ssl://' . SMTP_HOST;
    $smtpPort = SMTP_PORT;
    $username = SMTP_USER;
    $password = SMTP_PASS;
    $from = SMTP_FROM;
    $fromName = SMTP_FROM_NAME;
    
    // 创建连接
    $socket = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
    if (!$socket) {
        error_log("SMTP Connection Error: $errstr ($errno)");
        return false;
    }
    
    // 设置超时
    stream_set_timeout($socket, 30);
    
    // 读取欢迎消息
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '220') {
        fclose($socket);
        return false;
    }
    
    // EHLO
    fputs($socket, "EHLO " . SMTP_HOST . "\r\n");
    $response = '';
    while ($line = fgets($socket, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '334') {
        fclose($socket);
        return false;
    }
    
    // Username
    fputs($socket, base64_encode($username) . "\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '334') {
        fclose($socket);
        return false;
    }
    
    // Password
    fputs($socket, base64_encode($password) . "\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '235') {
        fclose($socket);
        return false;
    }
    
    // MAIL FROM
    fputs($socket, "MAIL FROM:<$from>\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        return false;
    }
    
    // RCPT TO
    fputs($socket, "RCPT TO:<$to>\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        return false;
    }
    
    // DATA
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '354') {
        fclose($socket);
        return false;
    }
    
    // 邮件头
    $headers = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\n";
    $headers .= "To: <$to>\r\n";
    $headers .= "Reply-To: <$replyTo>\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: base64\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "\r\n";
    
    // 邮件正文
    $encodedBody = chunk_split(base64_encode($body));
    
    // 发送邮件数据
    fputs($socket, $headers . $encodedBody . "\r\n.\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        return false;
    }
    
    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    return true;
}

/**
 * 发送邮件 (自动选择可用方法)
 */
function sendMail($to, $subject, $body, $replyTo, $replyToName) {
    // 优先使用 PHPMailer
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return sendMailWithPHPMailer($to, $subject, $body, $replyTo, $replyToName);
    }
    
    // 备用: 使用原生socket
    return sendMailWithSocket($to, $subject, $body, $replyTo);
}

// 设置编码
mb_language("Japanese");
mb_internal_encoding("UTF-8");

// 获取表单数据 - 基本信息
$customer = isset($_POST['customer']) ? htmlspecialchars($_POST['customer'], ENT_QUOTES, 'UTF-8') : '';
$customer_romaji = isset($_POST['customer_romaji']) ? htmlspecialchars($_POST['customer_romaji'], ENT_QUOTES, 'UTF-8') : '';
$gender = isset($_POST['gender']) ? htmlspecialchars($_POST['gender'], ENT_QUOTES, 'UTF-8') : '';

// 联系方式
$mailfrom = isset($_POST['mailfrom']) ? htmlspecialchars($_POST['mailfrom'], ENT_QUOTES, 'UTF-8') : '';
$mailfrom_confirm = isset($_POST['mailfrom_confirm']) ? htmlspecialchars($_POST['mailfrom_confirm'], ENT_QUOTES, 'UTF-8') : '';
$phone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8') : '';
$residence = isset($_POST['residence']) ? htmlspecialchars($_POST['residence'], ENT_QUOTES, 'UTF-8') : '';

// 校友信息
$is_alumni = isset($_POST['is_alumni']) ? htmlspecialchars($_POST['is_alumni'], ENT_QUOTES, 'UTF-8') : '';
$graduation_year = isset($_POST['graduation_year']) ? htmlspecialchars($_POST['graduation_year'], ENT_QUOTES, 'UTF-8') : '';
$degree = isset($_POST['degree']) ? htmlspecialchars($_POST['degree'], ENT_QUOTES, 'UTF-8') : '';
$department = isset($_POST['department']) ? htmlspecialchars($_POST['department'], ENT_QUOTES, 'UTF-8') : '';
$is_registered = isset($_POST['is_registered']) ? htmlspecialchars($_POST['is_registered'], ENT_QUOTES, 'UTF-8') : '';

// 咨询内容
$subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8') : 'general';
$inquiry_title = isset($_POST['inquiry_title']) ? htmlspecialchars($_POST['inquiry_title'], ENT_QUOTES, 'UTF-8') : '';
$message = isset($_POST['content']) ? htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8') : '';
$reply_method = isset($_POST['reply_method']) ? htmlspecialchars($_POST['reply_method'], ENT_QUOTES, 'UTF-8') : '';
$reply_urgency = isset($_POST['reply_urgency']) ? htmlspecialchars($_POST['reply_urgency'], ENT_QUOTES, 'UTF-8') : '';

// 其他信息
$how_found = isset($_POST['how_found']) ? htmlspecialchars($_POST['how_found'], ENT_QUOTES, 'UTF-8') : '';
$receive_newsletter = isset($_POST['receive_newsletter']) ? htmlspecialchars($_POST['receive_newsletter'], ENT_QUOTES, 'UTF-8') : '';

// 确认事项
$checkPrivate = isset($_POST['privacy_agree']) ? $_POST['privacy_agree'] : '';
$accuracy_agree = isset($_POST['accuracy_agree']) ? $_POST['accuracy_agree'] : '';

// 性别映射
$genderMap = array(
    'male' => '男',
    'female' => '女',
    'other' => '不愿透露'
);
$genderText = isset($genderMap[$gender]) ? $genderMap[$gender] : $gender;

// 居住地映射
$residenceMap = array(
    'tokyo' => '東京都',
    'kanagawa' => '神奈川県',
    'saitama' => '埼玉県',
    'chiba' => '千葉県',
    'osaka' => '大阪府',
    'kyoto' => '京都府',
    'aichi' => '愛知県',
    'hyogo' => '兵庫県',
    'fukuoka' => '福岡県',
    'hokkaido' => '北海道',
    'other_japan' => '日本其他地区',
    'china' => '中国',
    'other_overseas' => '其他海外地区'
);
$residenceText = isset($residenceMap[$residence]) ? $residenceMap[$residence] : $residence;

// 是否校友映射
$isAlumniMap = array(
    'yes' => '是',
    'no' => '否'
);
$isAlumniText = isset($isAlumniMap[$is_alumni]) ? $isAlumniMap[$is_alumni] : $is_alumni;

// 学历映射
$degreeMap = array(
    'non_alumni' => '非校友',
    'bachelor' => '本科毕业',
    'master' => '硕士毕业',
    'doctor' => '博士毕业',
    'exchange' => '交换留学生',
    'faculty' => '教职员工',
    'current_student' => '在校学生',
    'other' => '其他'
);
$degreeText = isset($degreeMap[$degree]) ? $degreeMap[$degree] : $degree;

// 是否已注册映射
$isRegisteredMap = array(
    'yes' => '已注册',
    'no' => '未注册',
    'unknown' => '不清楚'
);
$isRegisteredText = isset($isRegisteredMap[$is_registered]) ? $isRegisteredMap[$is_registered] : $is_registered;

// 主题映射
$subjectMap = array(
    'general' => '一般咨询',
    'membership' => '入会/会员相关咨询',
    'event' => '活动参加/活动相关咨询',
    'cooperation' => '企业/团体合作事宜',
    'council' => '理事会联系',
    'finance' => '财务报告相关',
    'website' => '网站相关问题',
    'suggestion' => '意见/建议',
    'volunteer' => '志愿者/参与校友会工作',
    'job' => '就职/招聘信息咨询',
    'newlife' => '新来日本生活咨询',
    'other' => '其他'
);
$subjectText = isset($subjectMap[$subject]) ? $subjectMap[$subject] : '一般咨询';

// 回复方式映射
$replyMethodMap = array(
    'email' => '邮件回复',
    'phone' => '电话回复',
    'both' => '都可以'
);
$replyMethodText = isset($replyMethodMap[$reply_method]) ? $replyMethodMap[$reply_method] : $reply_method;

// 回复紧急度映射
$replyUrgencyMap = array(
    'urgent' => '紧急（1-2个工作日内）',
    'normal' => '普通（1周内）',
    'not_urgent' => '不紧急（2周内即可）'
);
$replyUrgencyText = isset($replyUrgencyMap[$reply_urgency]) ? $replyUrgencyMap[$reply_urgency] : $reply_urgency;

// 如何得知映射
$howFoundMap = array(
    'search' => '搜索引擎（Google、百度等）',
    'wechat' => '微信',
    'friend' => '朋友介绍',
    'university' => '母校渠道',
    'event' => '活动现场',
    'other_alumni' => '其他校友会',
    'other' => '其他'
);
$howFoundText = isset($howFoundMap[$how_found]) ? $howFoundMap[$how_found] : $how_found;

// 是否接收通知映射
$receiveNewsletterMap = array(
    'yes' => '是，希望收到活动和新闻通知',
    'no' => '否，不需要'
);
$receiveNewsletterText = isset($receiveNewsletterMap[$receive_newsletter]) ? $receiveNewsletterMap[$receive_newsletter] : $receive_newsletter;

// 邮件标题
$title = "【西南交通大学日本校友会】网站留言 - " . $subjectText . " FROM " . $customer;

// 邮件正文
$content = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$content .= "西南交通大学日本校友会 网站留言\n";
$content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$content .= "■ 基本信息\n";
$content .= "────────────────────────────────\n";
$content .= "【姓名】" . $customer . "\n";
$content .= "【姓名（拼音/ローマ字）】" . $customer_romaji . "\n";
$content .= "【性别】" . $genderText . "\n\n";

$content .= "■ 联系方式\n";
$content .= "────────────────────────────────\n";
$content .= "【邮箱】" . $mailfrom . "\n";
$content .= "【电话号码】" . $phone . "\n";
$content .= "【现居住地】" . $residenceText . "\n\n";

$content .= "■ 校友信息\n";
$content .= "────────────────────────────────\n";
$content .= "【是否为西南交大校友】" . $isAlumniText . "\n";
$content .= "【毕业/在籍年份】" . $graduation_year . "\n";
$content .= "【学历/身份】" . $degreeText . "\n";
$content .= "【所属学院/专业】" . $department . "\n";
$content .= "【是否已注册校友会】" . $isRegisteredText . "\n\n";

$content .= "■ 咨询内容\n";
$content .= "────────────────────────────────\n";
$content .= "【咨询类别】" . $subjectText . "\n";
$content .= "【咨询标题】" . $inquiry_title . "\n";
$content .= "【希望的回复方式】" . $replyMethodText . "\n";
$content .= "【希望回复时间】" . $replyUrgencyText . "\n\n";
$content .= "【详细内容】\n";
$content .= "------------------------\n";
$content .= $message . "\n";
$content .= "------------------------\n\n";

$content .= "■ 其他信息\n";
$content .= "────────────────────────────────\n";
$content .= "【如何得知本网站】" . $howFoundText . "\n";
$content .= "【是否希望收到校友会通知】" . $receiveNewsletterText . "\n\n";

$content .= "■ 确认事项\n";
$content .= "────────────────────────────────\n";
$content .= "【个人信息使用同意】" . (!empty($checkPrivate) ? "已同意" : "未同意") . "\n";
$content .= "【信息准确性确认】" . (!empty($accuracy_agree) ? "已确认" : "未确认") . "\n\n";

$content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$content .= "【发送时间】" . date('Y-m-d H:i:s') . "\n";
$content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// 表单验证
$errors = array();

if (empty($customer)) {
    $errors[] = '请填写姓名';
}

if (empty($customer_romaji)) {
    $errors[] = '请填写姓名（拼音/ローマ字）';
}

if (empty($gender)) {
    $errors[] = '请选择性别';
}

if (empty($mailfrom)) {
    $errors[] = '请填写邮箱地址';
} elseif (!filter_var($mailfrom, FILTER_VALIDATE_EMAIL)) {
    $errors[] = '请填写有效的邮箱地址';
}

if ($mailfrom !== $mailfrom_confirm) {
    $errors[] = '两次输入的邮箱地址不一致';
}

if (empty($phone)) {
    $errors[] = '请填写电话号码';
}

if (empty($residence)) {
    $errors[] = '请选择现居住地';
}

if (empty($is_alumni)) {
    $errors[] = '请选择是否为西南交大校友';
}

if (empty($graduation_year)) {
    $errors[] = '请选择毕业/在籍年份';
}

if (empty($degree)) {
    $errors[] = '请选择学历/身份';
}

if (empty($department)) {
    $errors[] = '请填写所属学院/专业';
}

if (empty($is_registered)) {
    $errors[] = '请选择是否已注册校友会';
}

if (empty($subject)) {
    $errors[] = '请选择咨询类别';
}

if (empty($inquiry_title)) {
    $errors[] = '请填写咨询标题';
}

if (empty($message)) {
    $errors[] = '请填写详细内容';
} elseif (mb_strlen($message) < 30) {
    $errors[] = '详细内容至少需要30字';
}

if (empty($reply_method)) {
    $errors[] = '请选择希望的回复方式';
}

if (empty($reply_urgency)) {
    $errors[] = '请选择希望回复时间';
}

if (empty($how_found)) {
    $errors[] = '请选择如何得知本网站';
}

if (empty($receive_newsletter)) {
    $errors[] = '请选择是否希望收到校友会通知';
}

if (empty($checkPrivate)) {
    $errors[] = '请同意个人信息保护方针';
}

if (empty($accuracy_agree)) {
    $errors[] = '请确认信息准确性';
}

// 邮件发送结果
$mailSent = false;
$mailError = '';

if (empty($errors)) {
    $mailSent = sendMail(MAIL_TO, $title, $content, $mailfrom, $customer);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>留言发送结果 - 西南交通大学日本校友会</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .result-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .result-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .result-icon.success {
            color: #22c55e;
        }
        .result-icon.error {
            color: #ef4444;
        }
        .result-title {
            font-size: 24px;
            margin-bottom: 15px;
            color: #1e40af;
        }
        .result-message {
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .error-list {
            text-align: left;
            background: #fef2f2;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error-list li {
            color: #ef4444;
            margin-bottom: 5px;
        }
        .btn-back {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
    </style>
</head>
<body style="background: #f0f0f0;">
    <div class="result-container">
    <?php if (!empty($errors)): ?>
        <div class="result-icon error"><i class="fas fa-exclamation-circle"></i></div>
        <h2 class="result-title">发送失败</h2>
        <div class="error-list">
            <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <p class="result-message">请返回并修正以上问题后重新提交。</p>
    <?php elseif ($mailSent): ?>
        <div class="result-icon success"><i class="fas fa-check-circle"></i></div>
        <h2 class="result-title">发送成功</h2>
        <p class="result-message">感谢您的留言！<br>我们会尽快回复您。</p>
    <?php else: ?>
        <div class="result-icon error"><i class="fas fa-times-circle"></i></div>
        <h2 class="result-title">发送失败</h2>
        <p class="result-message">邮件发送失败，请稍后重试或直接发送邮件至 info@swjtu.jp</p>
    <?php endif; ?>
        <a href="../contact.html" class="btn-back"><i class="fas fa-arrow-left"></i> 返回联系页面</a>
    </div>
</body>
</html>
