<#assign actionKey="">
<#if link?? && link?contains("key=")>
  <#assign keyStart = link?index_of("key=") + 4>
  <#assign keyPart = link?substring(keyStart)>
  <#assign actionKey = keyPart?split("&")[0]>
</#if>
<#assign isVerifyEmail = requiredActions?? && requiredActions?seq_contains("VERIFY_EMAIL")>
<#if isVerifyEmail>
  <#assign actionUrl = properties.frontendUrl?trim?remove_ending("/") + "/verify-email?key=" + actionKey?url("UTF-8")>
  <#assign actionDescription = "Mở liên kết sau để xác minh email của bạn:">
  <#assign ignoreMessage = "Nếu bạn không tạo tài khoản này, vui lòng bỏ qua email.">
<#else>
  <#assign actionUrl = properties.frontendUrl?trim?remove_ending("/") + "/reset-password?key=" + actionKey?url("UTF-8")>
  <#assign actionDescription = "Mở liên kết sau để đặt lại mật khẩu:">
  <#assign ignoreMessage = "Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.">
</#if>
Xin chào ${user.username},

Quản trị viên vừa yêu cầu bạn cập nhật tài khoản ${realmName}.

${actionDescription}
${actionUrl}

Liên kết này sẽ hết hạn sau ${linkExpirationFormatter(linkExpiration)}.

${ignoreMessage}
