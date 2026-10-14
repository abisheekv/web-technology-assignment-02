<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%!
  private static String esc(String s) {
    if (s == null) return "";
    return s.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;").replace("\"", "&quot;");
  }
%>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In - KPRIET Portal</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="signin-page">
  <div class="signin-card">
    <div class="signin-head">
      <h2>KPRIET Student Portal</h2>
      <p>Secure Administrative Access</p>
    </div>
    <%
      String error = request.getParameter("error");
      String msg = request.getParameter("msg");
      if (error != null && !error.isEmpty()) {
    %>
      <div class="flash flash-bad"><%= esc(error) %></div>
    <% } if (msg != null && !msg.isEmpty()) { %>
      <div class="flash flash-good"><%= esc(msg) %></div>
    <% } %>
    <form action="signin" method="POST">
      <div class="entry">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter username" required autofocus>
      </div>
      <div class="entry">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required>
      </div>
      <button type="submit" class="btn-signin">Sign In</button>
    </form>
  </div>
</body>
</html>
