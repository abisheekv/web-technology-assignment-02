import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import javax.servlet.RequestDispatcher;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

public class StudentListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("user") == null) {
            response.sendRedirect("login.jsp?error=Please+sign+in+first");
            return;
        }

        // One row per student: latest photo id and one supporting document id (MIN avoids duplicate rows)
        String sql =
            "SELECT s.id AS student_id, s.name, s.roll_no, s.email, s.course, " +
            "  (SELECT MAX(d.id) FROM student_documents d " +
            "     WHERE d.student_id = s.id AND d.doc_category = 'profile_image') AS photo_id, " +
            "  (SELECT MIN(d.id) FROM student_documents d " +
            "     WHERE d.student_id = s.id AND d.doc_category <> 'profile_image') AS doc_id " +
            "FROM students s ORDER BY s.id DESC";

        List<Map<String, Object>> list = new ArrayList<>();
        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {

            while (rs.next()) {
                Map<String, Object> row = new LinkedHashMap<>();
                row.put("student_id", rs.getInt("student_id"));
                row.put("name", rs.getString("name"));
                row.put("roll_no", rs.getString("roll_no"));
                row.put("email", rs.getString("email"));
                row.put("course", rs.getString("course"));
                row.put("photo_id", rs.getObject("photo_id"));
                row.put("doc_id", rs.getObject("doc_id"));
                list.add(row);
            }
            request.setAttribute("studentList", list);
            RequestDispatcher rd = request.getRequestDispatcher("students.jsp");
            rd.forward(request, response);
        } catch (Exception e) {
            log("Could not load students", e);
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Unable to load student records.");
        }
    }
}
