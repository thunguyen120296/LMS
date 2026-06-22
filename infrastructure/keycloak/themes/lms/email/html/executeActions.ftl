<#assign actionKey="">
<#if link?? && link?contains("key=")>
  <#assign keyStart = link?index_of("key=") + 4>
  <#assign keyPart = link?substring(keyStart)>
  <#assign actionKey = keyPart?split("&")[0]>
</#if>
<#assign isVerifyEmail = requiredActions?? && requiredActions?seq_contains("VERIFY_EMAIL")>
<#if isVerifyEmail>
  <#assign actionUrl = properties.frontendUrl?trim?remove_ending("/") + "/verify-email?key=" + actionKey?url("UTF-8")>
  <#assign actionLabel = "Xác minh email">
  <#assign actionDescription = "Nhấn vào liên kết bên dưới để xác minh email của bạn:">
  <#assign ignoreMessage = "Nếu bạn không tạo tài khoản này, vui lòng bỏ qua email.">
<#else>
  <#assign actionUrl = properties.frontendUrl?trim?remove_ending("/") + "/reset-password?key=" + actionKey?url("UTF-8")>
  <#assign actionLabel = "Đặt lại mật khẩu">
  <#assign actionDescription = "Nhấn vào liên kết bên dưới để đặt lại mật khẩu:">
  <#assign ignoreMessage = "Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.">
</#if>
<html>
<body>
<p>Xin chào ${user.username},</p>
<p>Quản trị viên vừa yêu cầu bạn cập nhật tài khoản ${realmName}.</p>
<p>${actionDescription}</p>
<p><a href="${actionUrl}">${actionLabel}</a></p>
<p>Liên kết này sẽ hết hạn sau ${linkExpiration?datetime?string("dd/MM/yyyy HH:mm")}.</p>
<p>${ignoreMessage}</p>
</body>
</html>
