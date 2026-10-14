import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

public class StreamDocumentServlet extends HttpServlet {

    private static final int CHUNK = 4096;

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("user") == null) {
            response.sendError(HttpServletResponse.SC_UNAUTHORIZED, "Sign in required");
            return;
        }

        int docId;
        try {
            docId = Integer.parseInt(request.getParameter("id"));
        } catch (NumberFormatException e) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid document id");
            return;
        }

        String sql = "SELECT file_name, file_path, file_type FROM student_documents WHERE id = ?";
        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {

            ps.setInt(1, docId);
            try (ResultSet rs = ps.executeQuery()) {
                if (!rs.next()) {
                    response.sendError(HttpServletResponse.SC_NOT_FOUND, "Document not found");
                    return;
                }
                String name = rs.getString("file_name");
                String path = rs.getString("file_path");
                String type = rs.getString("file_type");

                File target = new File(getServletContext().getRealPath("/"), path);
                if (!target.isFile()) target = new File(path);
                if (!target.isFile()) {
                    response.sendError(HttpServletResponse.SC_NOT_FOUND, "File is missing from storage");
                    return;
                }

                boolean isImage = type != null && type.startsWith("image/");
                response.setContentType(type != null ? type : "application/octet-stream");
                response.setContentLengthLong(target.length());
                String safeName = name.replace("\"", "");
                response.setHeader("Content-Disposition",
                        (isImage ? "inline" : "inline; filename=\"" + safeName + "\""));
                response.setHeader("Cache-Control", "private, max-age=300");

                try (FileInputStream in = new FileInputStream(target);
                     OutputStream out = response.getOutputStream()) {
                    byte[] chunk = new byte[CHUNK];
                    int n;
                    while ((n = in.read(chunk)) > 0) {
                        out.write(chunk, 0, n);
                    }
                    out.flush();
                }
            }
        } catch (IOException e) {
            throw e;
        } catch (Exception e) {
            log("Streaming failed", e);
            if (!response.isCommitted()) {
                response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Could not stream file");
            }
        }
    }
}
