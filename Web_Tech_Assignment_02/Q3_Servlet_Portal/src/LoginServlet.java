import java.io.IOException;
import java.net.URLEncoder;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

public class LoginServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String uname = request.getParameter("username");
        String pword = request.getParameter("password");

        if (uname == null || pword == null || uname.trim().isEmpty() || pword.isEmpty()) {
            response.sendRedirect("login.jsp?error=" + URLEncoder.encode("Please enter both fields", "UTF-8"));
            return;
        }

        String sql = "SELECT username, full_name FROM users WHERE username = ? AND password = SHA2(?, 256)";
        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {

            ps.setString(1, uname.trim());
            ps.setString(2, pword);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    HttpSession old = request.getSession(false);
                    if (old != null) old.invalidate();              // avoid session fixation
                    HttpSession session = request.getSession(true);
                    session.setAttribute("user", rs.getString("username"));
                    session.setAttribute("fullName", rs.getString("full_name"));
                    response.sendRedirect("profiles");
                } else {
                    response.sendRedirect("login.jsp?error=" + URLEncoder.encode("Invalid username or password", "UTF-8"));
                }
            }
        } catch (SQLException e) {
            log("Login failure", e);
            response.sendRedirect("login.jsp?error=" + URLEncoder.encode("Service unavailable, try again later", "UTF-8"));
        }
    }
}
