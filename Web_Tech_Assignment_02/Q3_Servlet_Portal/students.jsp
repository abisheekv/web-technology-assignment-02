<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ page import="java.util.List, java.util.Map" %>
<%!
  private static String esc(Object o) {
    if (o == null) return "";
    return o.toString().replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;").replace("\"", "&quot;");
  }
%>
<%
  if (session == null || session.getAttribute("user") == null) {
    response.sendRedirect("login.jsp?error=Unauthorized+access");
    return;
  }
  String shownName = (String) session.getAttribute("fullName");
  if (shownName == null) shownName = (String) session.getAttribute("user");

  @SuppressWarnings("unchecked")
  List<Map<String, Object>> studentList = (List<Map<String, Object>>) request.getAttribute("studentList");
%>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Profiles - KPRIET</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <h1>KPRIET &ndash; Student Profiles Portal</h1>
      <div class="who">
        <span>Logged as: <strong><%= esc(shownName) %></strong></span>
        <a href="signout" class="btn-out">Logout</a>
      </div>
    </div>
  </header>

  <main class="main-area">
    <div class="heading">
      <h2>Verified Student Profiles (Dynamic Servlet Streaming)</h2>
      <p>Records loaded through JDBC; photos and documents are streamed by StreamDocumentServlet.</p>
    </div>

    <% if (studentList == null || studentList.isEmpty()) { %>
      <p>No student profiles available.</p>
    <% } else { %>
      <div class="card-grid">
      <%
        for (int i = 0; i < studentList.size(); i++) {
          Map<String, Object> s = studentList.get(i);
          Object photoId = s.get("photo_id");
          Object docId = s.get("doc_id");
          String name = (String) s.get("name");
      %>
        <div class="profile-card">
          <div class="photo-strip">
            <% if (photoId != null) { %>
              <img src="stream?id=<%= photoId %>" alt="Photo of <%= esc(name) %>" class="round-photo">
            <% } else { %>
              <div class="round-fallback"><%= esc(name.substring(0, 1).toUpperCase()) %></div>
            <% } %>
          </div>
          <div class="card-body">
            <h3><%= esc(name) %></h3>
            <span class="roll-tag"><%= esc(s.get("roll_no")) %></span>
            <p class="course-line"><%= esc(s.get("course")) %></p>
            <p class="mail-line">&#9993; <%= esc(s.get("email")) %></p>
            <div class="card-foot">
              <% if (docId != null) { %>
                <a href="stream?id=<%= docId %>" target="_blank" class="btn-doc">View Uploaded Document</a>
              <% } else { %>
                <span class="no-doc">No Document Attached</span>
              <% } %>
            </div>
          </div>
        </div>
      <% } %>
      </div>
    <% } %>
  </main>
</body>
</html>
